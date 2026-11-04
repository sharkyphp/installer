<?php
/**
 * 工具集基类
 *
 * @author Sharky
 */
declare (strict_types = 1);

namespace App\Console\Toolkits;

use Sharky\Console\Context\Input;
use Sharky\Console\Context\Output;
use Sharky\Foundation\Application;
use Sharky\Facade\Clock;

class DemoToolkit
{
    public function helloWord(Input $argvInput)
    {

        $hello = <<<EOT
    __         ____         _____ __               __
   / /_  ___  / / /___     / ___// /_  ____ ______/ /____  __
  / __ \/ _ \/ / / __ \    \__ \/ __ \/ __ `/ ___/ //_/ / / /
 / / / /  __/ / / /_/ /   ___/ / / / / /_/ / /  / ,< / /_/ /
/_/ /_/\___/_/_/\____/   /____/_/ /_/\__,_/_/  /_/|_|\__, /
                                                    /____/
EOT;
        $out = new Output();
        $out->setVerbosity(Output::VERBOSITY_DEBUG);
        $out->debugLn(sprintf("%s\n", $hello));
        $out->infoLn(sprintf("%s:    %s", 'Author', Application::AUTHOR));
        $out->infoLn(sprintf("%s:   %s", 'Project', Application::PROJECT));
        $out->infoLn(sprintf("%s:   %s", 'Version', Application::VERSION));
        $out->infoLn(sprintf(Application::COPYRIGHT, Clock::now()->format('Y')));
        $out->infoLn("");
    }
}
