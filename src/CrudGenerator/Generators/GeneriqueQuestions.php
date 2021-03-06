<?php

namespace CrudGenerator\Generators;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Output\OutputInterface;
use CrudGenerator\DataObject;

class GeneriqueQuestions
{
    /**
     * @var DialogHelper
     */
    private $dialog            = null;
    /**
     * @var OutputInterface
     */
    private $output            = null;
    /**
     * @var string
     */
    private $directoryResponse = null;
    /**
     * @var string
     */
    private $namespaceResponse = null;

    /**
     * @param DialogHelper $dialog
     * @param OutputInterface $output
     */
    public function __construct(DialogHelper $dialog, OutputInterface $output)
    {
        $this->dialog = $dialog;
        $this->output = $output;
    }

    /**
     * @param DataObject $dataObject
     * @throws \InvalidArgumentException
     * @return string
     */
    public function directoryQuestion(DataObject $dataObject)
    {
        if (null === $this->directoryResponse) {
            $moduleName = $dataObject->getModule();
            $directoryValidation = function ($directory) use ($moduleName) {
                if (!is_dir($moduleName . '/' . $directory)) {
                    throw new \InvalidArgumentException(
                        sprintf('Directory "%s" does not exist.', $moduleName . $directory)
                    );
                }

                return $directory;
            };

            $this->directoryResponse = $this->dialog->askAndValidate(
                $this->output,
                'Choose a target directory ',
                $directoryValidation,
                false
            );
        }

        return $this->directoryResponse;
    }

    /**
     * @return string
     */
    public function namespaceQuestion()
    {
        if (null === $this->namespaceResponse) {
            $this->namespaceResponse   = $this->dialog->ask($this->output, 'Choose a target namespace ');
        }

        return $this->namespaceResponse;
    }
}
