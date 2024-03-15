<?php

/*
 * This file is part of the Auburnite package.
 *
 * (c) Jordan Wamser <jwamser@redpandacoding.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Auburnite\Component\Malketa\src\Auburnite\Component\Melketa\Maker;

use Doctrine\DBAL\Types\Type;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @method string getCommandDescription()
 */
class AliasTypeMaker extends AbstractMaker
{
    public static function getCommandName(): string
    {
        return 'malketa:make:type';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new Doctrine Type class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('name', InputArgument::REQUIRED, sprintf(
                'Class name of the DBAL Type to create or update (e.g. <fg=yellow>%s</>)',
                Str::asClassName(Str::getRandomTerm())
            ))
            ->addArgument('base', InputArgument::OPTIONAL, sprintf(
                'Base Class name of the DBAL Type to create or update (e.g. <fg=yellow>%s</>)',
                Type::class
            ))
            ->addOption('namespace', null, null, sprintf(
                'The namespace prefix to create the class in (e.g. <fg=yellow>%s</>)',
                'Doctrine\\Types\\'
            ), 'Doctrine\\Types\\')

//            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeCommand.txt'))
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies
            ->addClassDependency(
                Type::class,
                'doctrine/dbal'
            );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $namespace = $input->getOption('namespace');
        $baseClass = $input->getArgument('base');
        $entityClassDetails = $generator->createClassNameDetails(
            $input->getArgument('name'),
            $namespace
        );

        $classExists = class_exists($entityClassDetails->getFullName());
        if ($classExists) {
            $entityPath = $this->getPathOfClass($entityClassDetails->getFullName());
            $io->text([
                'Your Type class already exists! We will have to rebuild. [FEATURE: Not Ready Yet, Sorry]',
            ]);

            return;
        }

        $io->text([
            'Generating your Type Class now...',
        ]);
    }

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method string getCommandDescription()
    }

    private function getTypesMap(): array
    {
        return Type::getTypesMap();
    }
}
