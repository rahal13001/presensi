<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class SignatureField extends Field
{
    protected string $view = 'forms.components.signature-field';

    protected string $penColor = '#000000';

    protected string $backgroundColor = 'transparent';

    protected int $canvasHeight = 200;

    protected float $lineWidth = 2.5;

    public function penColor(string $color): static
    {
        $this->penColor = $color;

        return $this;
    }

    public function backgroundColor(string $color): static
    {
        $this->backgroundColor = $color;

        return $this;
    }

    public function canvasHeight(int $height): static
    {
        $this->canvasHeight = $height;

        return $this;
    }

    public function lineWidth(float $width): static
    {
        $this->lineWidth = $width;

        return $this;
    }

    public function getPenColor(): string
    {
        return $this->penColor;
    }

    public function getBackgroundColor(): string
    {
        return $this->backgroundColor;
    }

    public function getCanvasHeight(): int
    {
        return $this->canvasHeight;
    }

    public function getLineWidth(): float
    {
        return $this->lineWidth;
    }
}
