<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\CoreBundle\Command;

use Doctrine\ORM\Mapping\ClassMetadata;
use Gaufrette\Adapter\Local as LocalAdapter;
use Gaufrette\Filesystem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Return useful data on the database schema.
 *
 * @deprecated since 3.x, to be removed in 4.0.
 */
final class SonataDumpDoctrineMetaCommand extends ContainerAwareCommand
{
    /**
     * @var array
     */
    private $metadata;

    protected function configure(): void
    {
        $this
            ->setName('sonata:core:dump-doctrine-metadata')
            ->setDefinition([
                new InputOption(
                    'entity-name',
                    'E',
                    InputOption::VALUE_OPTIONAL,
                    'If entity-name is set, dump will only contain the specified entity and all its extended classes.',
                    null
                ),
                new InputOption(
                    'regex',
                    'r',
                    InputOption::VALUE_OPTIONAL,
                    'If regex is set, dump will only contain entities which name match the pattern.',
                    null
                ),
                new InputOption(
                    'filename',
                    'f',
                    InputOption::VALUE_OPTIONAL,
                    'If filename is specified, result will be dumped into this file under json format.',
                    null
                ),
            ])
            ->setDescription('Get information on the current Doctrine\'s schema')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        @trigger_error(
            'The '.__CLASS__.' class is deprecated since version 3.x and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $output->writeln('Initialising Doctrine metadata.');
        $manager = $this->getContainer()->get('doctrine')->getManager();
        $metadata = $manager->getMetadataFactory()->getAllMetadata();

        $allowedMeta = $this->filterMetadata($metadata, $input);

        /** @var ClassMetadata $meta */
        foreach ($allowedMeta as $meta) {
            $this->metadata[$meta->getName()] = $this->normalizeDoctrineORMMeta($meta);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->metadata) {
            $output->writeln('<error>No meta was found</error>');

            return 1;
        }

        $this->dumpMetadata($this->metadata, $input, $output);

        return 0;
    }

    /**
     * Display the list of entities handled by Doctrine and their fields.
     */
    private function dumpMetadata(array $metadata, InputInterface $input, OutputInterface $output): void
    {
        foreach ($metadata as $name => $meta) {
            $output->writeln(sprintf('<info>%s</info>', $name));

            foreach ($meta['fields'] as $fieldName => $columnName) {
                $output->writeln(sprintf('  <comment>></comment> %s <info>=></info> %s', $fieldName, $columnName));
            }
        }
        $output->writeln('---------------');
        $output->writeln('----  END  ----');
        $output->writeln('---------------');
        $output->writeln('');

        if ($input->getOption('filename')) {
            $directory = \dirname($input->getOption('filename'));
            $filename = basename($input->getOption('filename'));

            if (empty($directory) || '.' === $directory) {
                $directory = getcwd();
            }

            $adapter = new LocalAdapter($directory, true);
            $fileSystem = new Filesystem($adapter);
            $success = $fileSystem->write($filename, json_encode($metadata), true);

            if ($success) {
                $output->writeLn(sprintf('<info>File %s/%s successfully created</info>', $directory, $filename));
            } else {
                $output->writeLn(sprintf('<error>File %s/%s could not be created</error>', $directory, $filename));
            }
        }
    }

    private function filterMetadata(array $metadata, InputInterface $input): array
    {
        $baseEntity = $input->getOption('entity-name');
        $regex = $input->getOption('regex');

        if ($baseEntity) {
            $allowedMeta = array_filter(
                $metadata,
                function (ClassMetadata $meta) use ($baseEntity): bool {
                    return $meta->rootEntityName === $baseEntity;
                }
            );
        } elseif ($regex) {
            $allowedMeta = array_filter(
                $metadata,
                function (ClassMetadata $meta) use ($regex): bool {
                    return (bool) preg_match($regex, $meta->rootEntityName);
                }
            );
        } else {
            $allowedMeta = $metadata;
        }

        return $allowedMeta;
    }

    private function normalizeDoctrineORMMeta(ClassMetadata $meta): array
    {
        $normalizedMeta = [];
        $fieldMappings = $meta->fieldMappings;

        $normalizedMeta['table'] = $meta->table['name'];

        foreach ($fieldMappings as $field) {
            $normalizedMeta['fields'][$field['fieldName']] = $field['columnName'] ?? null;
        }

        return $normalizedMeta;
    }
}
