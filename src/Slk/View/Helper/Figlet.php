<?php
namespace Slk\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Text\Figlet\Figlet as FigletText;

class Figlet extends AbstractHelper
{
    public function __invoke($text, $encoding = 'UTF-8')
    {
        $figlet = new FigletText();
        if (!$text) {
            return $figlet;
        }

        return $figlet->render($text, $encoding = 'UTF-8');
    }
}
