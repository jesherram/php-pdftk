<?php
	
	declare(strict_types = 1);
	
	namespace Escolarte\PhpPdftk;
	
	final class Pdf implements PdfInterface
	{
		protected $tempPath = "";
		private array $options = [
			'command' => 'pdftk',
			'output'  => 'output.pdf'
		];
		private string $command = "";
		private string $tpmFile = "";
		
		public function __construct(
			private string|null $pdf = null,
			                    $options = array()
		) {
			$this->setOptions($options);
			$this->tempPath = tempnam(sys_get_temp_dir(), 'pdf');
			
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
			return exec($command) === "";
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
			
			file_put_contents($this->tempPath, $pdfData);
			
			$this->command .= " fill_form {$this->tempPath}";
			
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
			$this->tempPath = "{$this->tempPath}/{$this->output}";
			$command        = " output {$this->tempPath}";
			
			return $this->execCommand($command);
		}
		
		/**
		 * @param string $output
		 * @return bool
		 */
		public function saveAs(string $output): bool
		{
			$command = " output {$output}";
			
			if ( $this->execCommand($command) )
				return unlink($this->tempPath);
			
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