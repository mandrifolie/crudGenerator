<?php

namespace CrudGenerator\Command;

use CrudGenerator\MetaData\MetaDataDAOFactory;
use CrudGenerator\Generators\CodeGeneratorFactory;
use CrudGenerator\DataObject;
use CrudGenerator\FileManager;
use CrudGenerator\MetaData\Config\MetaDataConfigReaderFactory;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use RuntimeException;
use InvalidArgumentException;

class CreateCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $this->setName('CodeGenerator:create')
             ->setDescription('Generate code based on database connection');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws RuntimeException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog      = $this->getHelperSet()->get('dialog');
        $fileManager = new FileManager();

        $adapter             = $this->adapterQuestion($output, $dialog);
        $adapterConfig       = $adapter->getConfig();
        $adapterConfigurator = MetaDataConfigReaderFactory::getInstance($output, $dialog);

        if(null !== $adapterConfig) {
            $adapterConfig = $adapterConfigurator->config($adapterConfig);
        }

        $adapterFactory = $adapter->getFactory();
        if(null !== $adapterConfig) {
            $adapterDAO = $adapterFactory::getInstance($adapterConfig);
        } else {
            $adapterDAO = $adapterFactory::getInstance();
        }

        $entity     = $this->entityQuestion($output, $dialog, $adapterDAO->getAllMetadata());
        $moduleName = $this->moduleQuestion($output, $dialog);
        $generator  = $this->generatorQuestion($output, $input, $dialog);

        $dataObject = new DataObject();
        $dataObject->setEntity($entity)
                   ->setModule($moduleName)
                   ->setMetaData($adapterDAO->getMetadataFor($entity));

        $crudGenerator = CodeGeneratorFactory::getInstance($output, $input, $dialog, $generator);

        $output->writeln("<info>Resume</info>");
        $output->writeln('<info>Entity : ' . $dataObject->getEntity(), '*</info>');
        $output->writeln('<info>Module : ' . $dataObject->getModule(), '*</info>');
        $output->writeln("<info>Generator : " . $crudGenerator->getDefinition(), "*</info>");

        $doI = $dialog->askConfirmation(
            $output,
            "\n<question>Do you confirm generation (may others question generator ask you) ?</question> "
        );

        if ($doI === true) {
            if (!is_dir('data/crudGeneratorHistory')) {
                $fileManager->mkdir('data/crudGeneratorHistory');
            }
            if (is_file('data/crudGeneratorHistory/' . md5($entity))) {
                unlink('data/crudGeneratorHistory/' . md5($entity));
            }
            $fileManager->filePutsContent('data/crudGeneratorHistory/' . md5($entity), serialize($dataObject));

            $crudGenerator->generate($dataObject);
        } else {
            throw new RuntimeException('Command aborted');
        }
    }

    /**
     * @param OutputInterface $output
     * @param DialogHelper $dialog
     * @throws \InvalidArgumentException
     * @return AdapterDataObject
     */
    private function adapterQuestion($output, $dialog)
    {
        $adapterFinder   = new \CrudGenerator\Adapter\AdapterFinder();
        $adaptersCollection = $adapterFinder->getAllAdapters();
        $output->writeln('<question>Adapters list</question>');
        foreach($adaptersCollection as $adapter) {
            $falseDependencies = $adapter->getFalseDependencies();

            if(!empty($falseDependencies)) {
                $output->writeln('<error>Dependencies not complet for use adapter "' . $adapter->getName() . '" caused by</error>');
                $output->writeln('<error> * ' . $falseDependencies . '</error>');
            } else {
                $output->writeln('<comment>' . $adapter->getDefinition() . '</comment>');
                $adaptersChoices[] = $adapter->getName();
            }
        }

        $adapterValidation = function ($adapter) use ($adaptersChoices, $adaptersCollection) {
            foreach($adaptersCollection as $adapterDataobject) {
                $adapterName = $adapterDataobject->getName();
                if($adapterName === $adapter) {
                    return $adapterDataobject;
                }
            }

            throw new \InvalidArgumentException(sprintf('Adapter "%s" is invalid.', $adapter));
        };

        return $dialog->askAndValidate(
            $output,
            "Choose an adapter \n> ",
            $adapterValidation,
            false,
            null,
            $adaptersChoices
        );
    }

    /**
     * @param OutputInterface $output
     * @param DialogHelper $dialog
     * @throws \InvalidArgumentException
     * @return string
     */
    private function moduleQuestion($output, $dialog)
    {
        $output->writeln('<question>Modules list</question>');

        $modulesChoices = glob('module/*');
        foreach ($modulesChoices as $moduleName) {
            $output->writeln('<comment>  ' . $moduleName . '</comment>');
        }

        $modulesValidation = function ($module) use ($modulesChoices) {
            if (!in_array($module, array_values($modulesChoices))) {
                throw new \InvalidArgumentException(sprintf('Module "%s" is invalid.', $module));
            }

            return $module;
        };

        return $dialog->askAndValidate(
            $output,
            "Choose a target module \n> ",
            $modulesValidation,
            false,
            null,
            $modulesChoices
        );
    }

    /**
     * @param OutputInterface $output
     * @param InputInterface $input
     * @param DialogHelper $dialog
     * @throws \InvalidArgumentException
     * @return string
     */
    private function generatorQuestion($output, $input, $dialog)
    {
        $crudFinder = new \CrudGenerator\Generators\GeneratorFinder();
        $generators = $crudFinder->getAllClasses();

        $output->writeln('<question>Chose a generator</question>');
        foreach ($generators as $number => $generatorClassName) {
            $generator = CodeGeneratorFactory::getInstance($output, $input, $dialog, $generatorClassName);
            $output->writeln('<comment>' . $number . '. ' . $generator->getDefinition() . '</comment>');
        }

        $generatorsValidation = function ($module) use ($generators) {
            if (!isset($generators[$module])) {
                throw new \InvalidArgumentException(sprintf('Generator "%s" is invalid.', $module));
            }

            return $generators[$module];
        };

        return $dialog->askAndValidate($output, "Choose a generators \n> ", $generatorsValidation, false);
    }

    /**
     * @param OutputInterface $output
     * @param DialogHelper $dialog
     * @param array $allMetaData
     * @throws InvalidArgumentException
     * @return string
     */
    private function entityQuestion($output, $dialog, $allMetaData)
    {
        $output->writeln('<question>Entities list</question>');
        $entityChoices = array();
        foreach ($allMetaData as $number => $class) {
            $output->writeln('<comment>  ' . $class->getName() . '</comment>');
            $entityChoices[] = $class->getName();
        }

        $entityValidation = function ($entity) use ($entityChoices) {
            if (!in_array($entity, array_values($entityChoices))) {
                throw new InvalidArgumentException(sprintf('Entity "%s" is invalid.', $entity));
            }

            return $entity;
        };

        return $dialog->askAndValidate(
            $output,
            "Full namespace Entity \n> ",
            $entityValidation,
            false,
            null,
            $entityChoices
        );
    }
}
