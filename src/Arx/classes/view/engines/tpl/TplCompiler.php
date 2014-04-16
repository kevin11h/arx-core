<?php namespace Arx\classes\view\engines\tpl;

use Closure;
use Illuminate\Filesystem\Filesystem;

use Illuminate\View\Compilers\BladeCompiler as ParentClass;

/**
 * Class TplCompiler
 *
 * The same than BladeCompiler but instead of using {{ }} he use <% %> to avoid conflict with Angular, Handlebar and other JS engine
 *
 * Extension is tpl.php or tpl.js
 *
 * @package Arx\classes\view\engines\tpl
 */
class TplCompiler extends ParentClass {

    /**
     * Array of opening and closing tags for echos.
     *
     * @var array
     */
    protected $contentTags = array('<%', '%>');

    /**
     * Array of opening and closing tags for escaped echos.
     *
     * @var array
     */
    protected $escapedTags = array('<%=', '%>');

    /**
     * Compile Tpl comments into valid PHP.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComments($value)
    {
        // comment with < %-- --% > or <%# % >

        $pattern = sprintf('/%s#((.|\s)*?)%s/', $this->contentTags[0], $this->contentTags[1]);

        return preg_replace($pattern, '<?php /* $1 */ ?>', $value);
    }

}