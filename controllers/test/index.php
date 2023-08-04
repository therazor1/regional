<?php namespace Controllers\test;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use RegexIterator;

class index extends _controller
{

    public function index()
    {
        $classes = $this->getClasses();

        echo '<ul>';

        foreach ($classes as $clazz) {
            if (!in_array($clazz, ['index', '_controller'])) {
                echo '
                    <li>
                        <a href="' . URL_API . '/test/' . $clazz . '">' . $clazz . '</a>
                    </li>
                ';
            }
        }

        echo '</ul>';
    }

    private function getClasses()
    {
        $path = __DIR__;
        $fqcns = array();

        $allFiles = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        $phpFiles = new RegexIterator($allFiles, '/\.php$/');
        foreach ($phpFiles as $phpFile) {
            $content = file_get_contents($phpFile->getRealPath());
            $tokens = token_get_all($content);
            $namespace = '';
            for ($index = 0; isset($tokens[$index]); $index++) {
                if (!isset($tokens[$index][0])) {
                    continue;
                }
                if (T_NAMESPACE === $tokens[$index][0]) {
                    $index += 2; // Skip namespace keyword and whitespace
                    while (isset($tokens[$index]) && is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }
                if (T_CLASS === $tokens[$index][0] && T_WHITESPACE === $tokens[$index + 1][0] && T_STRING === $tokens[$index + 2][0]) {
                    $index += 2; // Skip class keyword and whitespace
                    $fqcns[] = $tokens[$index][1];

                    # break if you have one class per file (psr-4 compliant)
                    # otherwise you'll need to handle class constants (Foo::class)
                    break;
                }
            }
        }

        return $fqcns;
    }

}