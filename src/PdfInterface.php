<?php

declare(strict_types = 1);
	
namespace Escolarte\PhpPdftk;

interface PdfInterface
{
	public function __construct(string|null $pdf, array $options);
    
    public function getErrors(): array;
	
	public function __get(string $name): mixed;
	
	public function __set(string $option, mixed $value): void;
	
	public function fillForm(array $data): Pdf;
	
	public function flatten(): Pdf;
	
	public function execute(): bool;
	
	public function saveAs(string $output): bool;
	
	public function getTpmFIle(): string;
}