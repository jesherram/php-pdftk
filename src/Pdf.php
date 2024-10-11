<?php

declare(strict_types = 1);

namespace Escolarte\PhpPdftk;

final class Pdf implements PdfInterface
{
    public string $tempPath = "";
    private string $tpmFile = "";
    private array $errors = [];
    private array $options = [
        'command' => 'pdftk',
        'output'  => 'output.pdf'
    ];
    private string $command = "";

    public function __construct(
        private string|null $pdf = null,
                            $options = array()
    ) {
        $this->setOptions($options);
        $this->tempPath = sys_get_temp_dir();

        $this->command = "{$this->command} {$this->pdf}";
    }

    /**
     * @param array $options
     * @return void
     */
    private function setOptions(array $options): void
    {
        foreach ($options as $option => $value) {
            if ( $this->isValidOption($option) ) $this->$option = $value;
        }
    }

    /**
     * @param string $option
     * @return bool
     */
    private function isValidOption(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * @param string $command
     * @return bool
     */
    private function execCommand(string $command): bool
    {
        exec($command, $output, $returnVar);

        $success = count($output) === 0;

        if (! $success ) $this->errors = $output;

        return $success;
    }

    /**
     * @return string
     */
    private function getTmpNamFdf(): string
    {
        if (! $this->tempPath)
            $this->tempPath = sys_get_temp_dir();

        return tempnam($this->tempPath, 'fdf');
    }

    /**
     * @param string $output
     * @return void
     */
    private function addCommandOutput(string $output): void
    {
        if (stripos($this->command, 'flatten') !== false) {
            $this->command = str_ireplace('flatten', "{$output} flatten", $this->command);
        } else {
            $this->command .= " {$output}";
        }
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->options[$name];
    }

    /**
     * @param string $option
     * @param mixed $value
     * @return void
     */
    public function __set(string $option, mixed $value): void
    {
        if ( $this->isValidOption($option) ) $this->options[$option] = $value;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fillForm(array $data): Pdf
    {
        $pdfData = "%FDF-1.2\n1 0 obj\n<< /FDF << /Fields [\n";

        foreach ($data as $key => $value) {
            $pdfData .= "<< /T ({$key}) /V ({$value}) >>\n";
        }

        $pdfData .= "] >> >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";

        file_put_contents($this->getTmpNamFdf(), $pdfData);

        $this->command .= " fill_form {$this->getTmpNamFdf()}";

        return $this;
    }

    /**
     * @return $this
     */
    public function flatten(): Pdf
    {
        $this->command .= " flatten";

        return $this;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $this->tpmFile = tempnam($this->tempPath, 'pdf');

        $this->addCommandOutput("output {$this->tpmFile}");

        return $this->execCommand($this->command);
    }

    /**
     * @param string $output
     * @return bool
     */
    public function saveAs(string $output): bool
    {
        $this->addCommandOutput("output {$output}");

        if ( $this->execCommand($this->command) ) {
            if ( file_exists($this->tpmFile) )
                return unlink($this->tpmFile);

            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getTpmFIle(): string
    {
        return $this->tpmFile;
    }
}
