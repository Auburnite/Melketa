<?php

/*******************************************************************************
 * Auburnite
 *
 * @link                https://github.com/Auburnite/Auburnite
 * @copywrite           Copywrite (c) 2023-present | Jordan Wamser - RedPanda Coding
 * @license             https://github.com/Auburnite/Auburnite/blob/main/LICENSE
 ******************************************************************************/
namespace Auburnite\Component\Malketa\Command;

use Auburnite\Component\Malketa\Generator\ColumnOptionConfigGenerator;
use Auburnite\Component\Malketa\Models\SchemaUserDefinedType;
use Auburnite\Component\Malketa\UserDefinedTypeManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\UseStatementGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Terminal;

class UserDefinedTypeLoaderCommand extends Command
{
//    private UserDefinedTypeManager $definedTypeManager;
    private array $consoleMessages;
    private OutputInterface $output;

    public function __construct(
        private Generator $generator,
        private UserDefinedTypeManager $definedTypeManager
    ){
        //Database configuration
//        $this->definedTypeManager = new UserDefinedTypeManager($connection);

        parent::__construct(self::getDefaultName());
    }

    public static function getDefaultName(): ?string
    {
        return 'metadata:generate';
    }

    public static function getDefaultDescription(): ?string
    {
        return 'Melketa Tool to help load in MSSQL UserDefinedTypes. This will help create the doctrine Type Classes.';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Apply a style reminiscent of Oregon Trail
        $style = new OutputFormatterStyle('green', 'black', ['bold']);
        $output->getFormatter()
            ->setStyle('trail', $style);
        $this->output = $output;

//        // Define a custom style with blinking
//        $blinkingStyle = new OutputFormatterStyle('green', 'black', ['options' => ['blink']]);
//        $output->getFormatter()->setStyle('blink', $blinkingStyle);

        $terminal = new Terminal();
        $width = $terminal->getWidth();
        $height = $terminal->getHeight();

//        $message = 'Hello, Symfony Terminal!';
//        $x = ($width - strlen($message)) / 2;
//        $y = $height / 2;

        // Display an ASCII art title
        $title = <<<ASCII
   ▄▄▄▄███▄▄▄▄      ▄████████  ▄█          ▄█   ▄█▄    ▄████████     ███        ▄████████
 ▄██▀▀▀███▀▀▀██▄   ███    ███ ███         ███ ▄███▀   ███    ███ ▀█████████▄   ███    ███
 ███   ███   ███   ███    ███ ███         ███▐██▀     ███    █▀     ▀███▀▀██   ███    ███
 ███   ███   ███   ███    ███ ███         ███▐██▀     ███    █▀     ▀███▀▀██   ███    ███
 ███   ███   ███   ███    ███ ███        ▄█████▀     ▄███▄▄▄         ███   ▀   ███    ███
 ███   ███   ███ ▀███████████ ███       ▀▀█████▄    ▀▀███▀▀▀         ███     ▀███████████
 ███   ███   ███   ███    ███ ███         ███▐██▄     ███    █▄      ███       ███    ███
 ███   ███   ███   ███    ███ ███▌    ▄   ███ ▀███▄   ███    ███     ███       ███    ███
  ▀█   ███   █▀    ███    █▀  █████▄▄██   ███   ▀█▀   ██████████    ▄████▀     ███    █▀.
                              ▀           ▀                                             .
ASCII;

//        $output->writeln("<trail>$title</trail>");

//        $output->write(sprintf("\033[%d;%dH%s", $y, $x, "<trail>$title</trail>"));
        // Clear the screen
        $this->output->write("\033[2J\033[H");
        $this->output->writeln(sprintf("%s", "<trail>$title</trail>"));

        // Wait for user input
        $init = true;
        $helpMessages = [
            "[Type ':m' to see metadata] ",
            "[Type ':g' to generate Type Classes] ",
            "[Type ':h' to See these command options] ",
        ];
        do {
            $clear = true;

            if ($init) {
                $this->consoleMessages = $helpMessages;
                $init = false;
            }

            $this->displayConsoleMessages();

            $actionInput = readline("Type ':q' to quit: ");

            if ($actionInput === ':g') {
                $this->output->write("\033[2J\033[H");

                $types = $this->loadDbalUserDefinedTypes();

                $this->output->write("\033[2J\033[H");
                $this->output->writeln('<trail>Loading... Preparing File Generation</trail>');
                $sy = new SymfonyStyle($input,$this->output);
                $progress = $sy->createProgressBar(count($types));
                $progress->display();
                foreach ($types as $key => $type) {
                    $typeMEtadata = $this->createTypeMetadata($type);
                    $typeClassName = $this->generator->createClassNameDetails(
                        $typeMEtadata[1],
                        'Auburnite\Component\Malketa\Type\\',
                        'Type'
                    );
                    $useStatments = new UseStatementGenerator([
                        \Doctrine\DBAL\Platforms\AbstractPlatform::class,
                        $typeMEtadata[4],
                    ]);
                    $parts = explode('\\', $typeMEtadata[4]);
                    $shortName = end($parts);
                    $this->generator->generateFile(
                        __DIR__.'/../Type/'.$typeClassName->getShortName().'.php',
                        __DIR__.'/../Type/Type.tpl.php',
                        [
                            'class_name' => $typeClassName->getShortName(),
                            'namespace' => 'Auburnite\Component\Malketa\Type',
                            'use_statemnts' => $useStatments,
                            'base_class_name' => $shortName,
                            'column_settings' => new ColumnOptionConfigGenerator(
                                new SchemaUserDefinedType((array)$type),
                                $typeMEtadata[4]
                            ),
                            'name' => $typeMEtadata[1],
                        ]
                    );
                    $progress->advance();
                }
                $progress->finish();
                $this->output->write("\033[2J\033[H");
                $this->output->writeln('<trail>Loading... Generating Files</trail>');
                $this->generator->writeChanges();
                $this->output->write("\033[2J\033[H");

                $clear = false;
            }

            if ($actionInput === ':m') {
                $sy = new SymfonyStyle($input,$this->output);
                $table = $sy->createTable();
                $table->setHeaders([
                    'Supported',
                    'Type Name',
                    'Base Type Name',
                    'Doctrine Platform Base Type Mapping',
                    'Doctrine Type Registry Base Type Class*',
                ]);

                $this->output->write("\033[2J\033[H");
                $this->output->writeln('<trail>Platform Type Metadata</trail>');
                $table->addRows($this->loadTypesMetadata());
                $table->render();

                $this->consoleMessages += ['* = depends on isSupported column.'];

                $clear = false;
            }
        } while ($actionInput !== ':q');

        // Clear the screen
        if ($clear) {
            $this->output->write("\033[2J\033[H");
        }
        $this->displayConsoleMessages();

        return Command::SUCCESS;
    }

    private function loadTypesMetadata(): array
    {
        return array_map(function(SchemaUserDefinedType|\stdClass $type): array {
            return $this->createTypeMetadata($type);
        },$this->loadDbalUserDefinedTypes());
    }

    private function loadDbalUserDefinedTypes(): array
    {
        // Specify the file path where the JSON should be saved
        $filePath = __DIR__.'AliasTypes.lock';
        if (!file_exists($filePath)) {
            $this->output->write("\033[2J\033[H");
            $this->output->writeln('<trail>LOADING... From Database</trail>');
            $results = $this->definedTypeManager->fetchAliasTypes();
            $this->output->write("\033[2J\033[H");
            $this->output->writeln('<trail>LOADING... Types to Array</trail>');
            $types = array_map(function($row): SchemaUserDefinedType {
                $type = new SchemaUserDefinedType($row);
                $type->setSupport(
                    $this->definedTypeManager->getDatabasePlatform()->hasDoctrineTypeMappingFor(
                        $type->getBaseSystemTypeName()
                    )
                );

                return $type;
            },$results);

            // Encode the array into JSON format
            $jsonData = json_encode([
                'types'=>$types
            ]);

            $this->output->write("\033[2J\033[H");
            $this->output->writeln('<trail>LOADING... Cacheing Types</trail>');
            // Save the JSON data to a file
            file_put_contents($filePath, $jsonData);
            $this->consoleMessages[] = sprintf(
                "JSON data has been saved to `%s`",
                $filePath
            );
        }


        $this->output->write("\033[2J\033[H");
        $this->output->writeln('<trail>LOADING... From Cache</trail>');
        // Reading the file content
        $jsonString = file_get_contents($filePath);

        // Decoding the JSON string into an object
        $this->output->write("\033[2J\033[H");
        $this->output->writeln('<trail>LOADING...Encoding Cache</trail>');
        $types = (json_decode($jsonString))->types;

        $this->consoleMessages[] = "Total Types Found: ".count($types);


        $this->output->write("\033[2J\033[H");
        return $types;
    }

    private function createTypeMetadata(SchemaUserDefinedType|\stdClass $type)
    {
        if ($type instanceof \stdClass) {
            $type = new SchemaUserDefinedType((array)$type);
        }

        return [
            $type->isSupported(),
            $type->getName(),
            $type->getBaseSystemTypeName(),
            $this->definedTypeManager->getDatabasePlatform()->getDoctrineTypeMapping(
                $type->getBaseSystemTypeName()
            ),
            $type->isSupported() ?
                get_class(Type::getType(
                    $this->definedTypeManager->getDatabasePlatform()->getDoctrineTypeMapping(
                        $type->getBaseSystemTypeName()
                    )
                )) : 'N/A',
        ];
    }

    private function displayConsoleMessages(): void
    {
        if (0 < count($this->consoleMessages)) {
            $this->output->writeln($this->consoleMessages);
            $this->consoleMessages = [];
        }
    }
}
