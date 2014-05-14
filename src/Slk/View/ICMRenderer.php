<?php
namespace Slk\View;

use Zend\Console\Adapter\AdapterInterface;
use Zend\View\Renderer\PhpRenderer;

/*
 * Interactive Console Markup Renderer
 */
class ICMRenderer extends PhpRenderer
{

    /**
     *
     * @var AdapterInterface
     */
    protected $console;

    public function setConsoleAdapter(AdapterInterface $console)
    {
        $this->console = $console;
    }

    /*
     * (non-PHPdoc) @see \Zend\View\Renderer\RendererInterface::render()
     */
    public function render($nameOrModel, $values = null)
    {
        $globalVariables = array(
            'script' => $_SERVER['PHP_SELF']
        );
        $text = parent::render($nameOrModel, $values);

        $lines = explode("\n", $text);
        $result = null;
        $title = '';
        $catchTitle = false;

        for ($i=0, $max=count($lines); $i<$max; $i++) {
            $line = $lines[$i];
            $globalVariables['result'] = $result;
            $line = $this->parseVariables($line, $globalVariables);

            if (preg_match('/^\s*\[{3,}\s*$/', $line)) {
                $catchTitle = true;
                continue;
            }

            if ($catchTitle && preg_match('/^\s*\]{3,}\s*$/', $line)) {
                $catchTitle = false;
                continue;
            }

            if ($catchTitle) {
                $title .= $line."\n";
                continue;
            }

            if (preg_match('/^\s*\-{3,}\s*$/', $line)) {
                $this->console->write("--- Press enter to continue.");
                $this->console->readLine();
                $this->console->clearLine();
                continue;
            }

            if (preg_match('/^\s*>{3,}(\${0,1})\s*$/', $line, $matches)) {
                if ($matches[1]) {
                    $this->console->write(">>>> Press enter to go to the next page.");
                    $result = $this->console->readLine();
                    $this->console->clearLine();
                };

                $this->console->clear();
                if ($title) {
                    $this->console->write($title);
                }
                continue;
            }

            if (preg_match('/^\s*\$_(.*?)$/', $line, $matches)) {
                $result = $this->safeShellExec($matches[1]);
                continue;
            } else
                if (preg_match('/^\s*>(\${0,1})_\s*$/', $line, $matches)) {
                    // make prompt
                    $prompt = "> ";
                    if ($matches[1]) {
                        $prompt .= $globalVariables['script']." ";
                    }

                    $expectedValue = '';
                    $example = '';
                    // check if there are expected values
                    if (preg_match('/^\s*==\s*(.*?)$/', $lines[$i+1], $matches)) {
                        $i++;
                        $expectedValue = trim($matches[1]);
                    }

                    if (preg_match('/^\s*===\s*(.*?)$/', $lines[$i+1], $matches)) {
                        $i++;
                        $example = trim($matches[1]);
                    }

                    // check if the value should be stored in a variable
                    $variableName = '';
                    if (preg_match('/^\s*=>\s*(\w+)$/', $lines[$i+1], $matches)) {
                        $i++;
                        $variableName = trim($matches[1]);
                    }

                    $tries = 3;
                    while ($tries) {
                        if (strlen($example)) {
                            $tries--;
                        }
                        $this->console->write($prompt);
                        $result = $this->console->readLine();

                        $noMatch = false;
                        if ($expectedValue) {
                            if (preg_match('!^\/(.*?)\/$!', $expectedValue, $resultMatches)) {

                                // check the result against the requested pattern
                                if (! preg_match('/' . $resultMatches[1] . '/', $result, $valueMatches)) {
                                    $noMatch = true;
                                } else {
                                    $globalVariables['matches'] = $valueMatches;
                                }
                            } else
                                if (trim($result) != $expectedValue) {
                                    $noMatch = true;
                                }

                            if ($noMatch) {
                                // we have to check the result against a condition.
                                $this->console->writeLine("The entered text did not match the allowed values!");
                                if (strlen($example)) {
                                    $this->console->writeLine("You have $tries more tries.");
                                }

                                continue;
                            }
                            break;
                        }
                    }

                    if (!$tries) {
                        $result = $example;
                        $example = "Expected result: ".$example;

                        $this->console->writeLine($example);
                    }

                    if ($variableName) {
                        $globalVariables[$variableName] = $result;
                    }

                    continue;
                }

            $this->console->writeLine($line);
        }
    }

    protected function parseVariables($line, array $variables)
    {
        $line = preg_replace_callback('#\$([A-z_][A-z0-9_]*)\[(.*?)\]#', function($matches) use ($variables) {
            $name = $matches[1];

            return @$variables[$name][$matches[2]];
        }, $line);

        return preg_replace_callback('#\$([A-z_][A-z0-9_]*)#', function($matches) use ($variables) {
            $name = $matches[1];
            if (isset($variables[$name])) {
                return $variables[$name];
            } else {
                return $matches[0];
            }

        }, $line);

        return $line;
    }

    /**
    /* Executes in a save way the shell command
     *
     * @param  string|array $args
     * @return string
     */
    protected function safeShellExec($args)
    {

        if (!is_array($args)) {
            $args = preg_split('/\s+/', trim($args));
        }
        $cmd = $_SERVER['PHP_SELF'].' '.implode(' ',array_map('escapeshellarg', $args));

        return shell_exec($cmd);
    }

}
