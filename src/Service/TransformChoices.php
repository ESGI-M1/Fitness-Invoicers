<?php
namespace App\Service;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

class TransformChoices implements ChoiceListInterface
{
    public function getChoices() { return []; }
    public function getValues() { return []; }
    public function getPreferredViews() { return []; }
    public function getRemainingViews() { return []; }
    public function getChoicesForValues(array $values) { return $values; }
    public function getValuesForChoices(array $choices) { return $choices; }
    public function getIndicesForChoices(array $choices) { return $choices; }
    public function getIndicesForValues(array $values) { return $values; }
}