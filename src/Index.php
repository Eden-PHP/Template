<?php //-->
/**
 * This file is part of the Eden PHP Library.
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Eden\Template;

/**
 * General available methods for common template procedures.
 *
 * @package  Eden
 * @category Template
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Index extends Base
{
    /**
     * @const string ENGINE_PATTERN Template variable regexp pattern
     */
    const ENGINE_PATTERN = '!{([@$#])([A-Za-z0-9:_]+)}|{([A-Za-z:_\!][A-Za-z0-9:_]*)(\s*,(.+?))?(/}|}(.*?){/\\3})!s';
    
    /**
     * @var array $data The data that will be bound in the template
     */
    protected $data = array();
       
    /**
     * @var function|null $callback The handler to call when we cannot find a template value
     */
    private $callback = null;
    
    /**
     * Sets template variables
     *
     * @param *array|string $data  data
     * @param mixed         $value value
     *
     * @return Eden\Template\Index
     */
    public function set($data, $value = null)
    {
        //argument 1 must be an array or string
        Argument::i()->test(1, 'array', 'string');

        if (is_array($data)) {
            $this->data = $data;
            return $this;
        }

        $this->data[$data] = $value;

        return $this;
    }
    
    /**
     * Engine Parser. This parser also cases for lazy loaded variables.
     * One problem with template engines is that it requires you to preload
     * variables. This becomes problematic when your template requires a
     * plethora of MySQL, Facebook, Twitter calls for example. Sometimes
     * it's just best to wait till it's needed.
     * ex {$title}
     * ex {products}{$title}{/products}
     *
     * @param *string       $template The template string
     * @param callable|null $callback Callback to be used when key does not exist in data
     *
     * @return string
     */
    public function parseEngine($template, $callback = null)
    {
        //argument test
        Argument::i()
            //argument 1 must be a string
            ->test(1, 'string')
            //argument 2 must be callable or null
            ->test(2, 'callable', 'null');
        
        $this->callback = $callback;
        
        return preg_replace_callback(
            self::ENGINE_PATTERN,
            array($this, 'engineParseResults'),
            $template
        );
    }

    /**
     * Simple string replace template parser
     *
     * @param *string $string The template string
     *
     * @return string
     */
    public function parseString($string)
    {
        //argument 1 must be a string
        Argument::i()->test(1, 'string');
        
        foreach ($this->data as $key => $value) {
            $string = str_replace($key, $value, $string);
        }

        return $string;
    }

    /**
     * For PHP templates, this will transform the given document to an actual page or partial
     *
     * @param *string $___file       Template file or PHP template string
     * @param bool    $___evalString Whether to evaluate the first argument
     *
     * @return string
     */
    public function parsePhp($___file, $___evalString = false)
    {
        Argument::i()
            //argument 1 must be a string
            ->test(1, $___file, 'string')
            //argument 2 must be a boolean
            ->test(2, $___evalString, 'bool');
        
        // Extract the values to a local namespace
        extract($this->data, EXTR_SKIP);

        if ($___evalString) {
            return eval('?>'.$___file.'<?php;');
        }
        
        // Start output buffering
        ob_start();
        // Include the template file
        include $___file;
        // Get the contents of the buffer
        $___contents = ob_get_contents();
        // End buffering and discard
        ob_end_clean();
        // Return the contents
        return $___contents;
    }
    
    /**
     * Recursively parses template variables
     * to eventually return a string considering
     * binded values
     *
     * @param *array $matches Matches usually given by a preg method
     *
     * @return string|null
     */
    protected function engineParseResults($matches)
    {
        switch (count($matches)) {
            case 3:
                if (!isset($this->data[$matches[2]])) {
                    if ($this->callback) {
                        return call_user_func($this->callback, $matches[2], $matches[1]);
                    }
                    
                    return null;
                }
                
                //if count
                if ($matches[1] == '#') {
                    switch (true) {
                        case is_numeric($this->data[$matches[2]]):
                            return $this->data[$matches[2]];
                        case is_string($this->data[$matches[2]]):
                            return strlen($this->data[$matches[2]]);
                        case is_array($this->data[$matches[2]]):
                            return count($this->data[$matches[2]]);
                        default:
                            return 0;
                    }
                }
                
                return $this->data[$matches[2]];
            case 7:
                //parse args
                $args = str_replace(array('  ',',', ' '), array(' ','', '&'), trim($matches[5]));
                parse_str($args, $args);
                
                if (!isset($this->data[$matches[3]])) {
                    if ($this->callback) {
                        return call_user_func($this->callback, $matches[3], '$', $args);
                    }
                    
                    return null;
                }
                
                return $this->data[$matches[3]];
            case 8:
                //if count test
                if (strpos($matches[3], '!') === 0) {
                    $key = substr($matches[3], 1);
                    
                    //if not exists
                    if (!isset($this->data[$key])
                    || !$this->data[$key]
                    || !count($this->data[$key])) {
                        //return blank
                        return null;
                    }
                    
                    //just blind pass
                    return self::i()->set($this->data)->parseEngine($matches[7]);
                }
                
                //parse args
                $args = str_replace(array('  ',',', ' '), array(' ','', '&'), trim($matches[5]));
                parse_str($args, $args);
                
                if (!isset($this->data[$matches[3]])) {
                    if ($this->callback) {
                        return call_user_func($this->callback, $matches[3], $matches[7], $args);
                    }
                    
                    return null;
                }
                
                $rows = array();
                foreach ($this->data[$matches[3]] as $j => $row) {
                    if (!is_array($row)) {
                        $rows[] = self::i()
                        ->set($this->data[$matches[3]])
                        ->parseEngine($matches[7]);
                        break;
                    }
                    
                    $rows[] = self::i()->set($row)->parseEngine($matches[7]);
                }
                
                return implode("\n", $rows);
            default:
                return null;
        }
    }
}
