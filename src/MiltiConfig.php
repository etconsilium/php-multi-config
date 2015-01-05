<?php namespace MultiConfig;

use \MultiConfig\Exception\ParseException;
use \MultiConfig\Exception\FileNotFoundException;
use \MultiConfig\Exception\UnsupportedFormatException;
use \Symfony\Component\Yaml\Yaml as Yaml;
use \CFPropertyList as Plist;

/**
 * Multi-Config
 *
 * @package    etconsilium\php-multi-config
 * @author     VS <etconsilium@users.noreply.github.com>
 * @link       https://github.com/etconsilium/php-multi-config
 * @license    MIT
 * @based      on https://github.com/noodlehaus/config
 */
class Config extends \RecursiveArrayObject
{
    /**
     * Caches the configuration data
     *
     * @var array
     */
//    protected $cache = array();

    /**
     * Realpath configuration file
     *
     * @var string
     */
    protected $filename = null;

    /**
     * Types: PHP, Ini, XML, JSON, YAML, Plist files and Array()
     */
    const ARR = 'Array';
    const CVS = 'Cvs';
    const INI = 'Ini';
    const PLST = 'Plist';
    const CFPL = 'Plist';
    const PHP = 'Php';
    const XML = 'Xml';
    const YAML = 'Yaml';

    protected $ext_list = [
        self::ARR => []
        ,self::CVS => ['cvs','txt','']
        ,self::INI => ['ini','init']
        ,self::PHP => ['php','inc']
        ,self::PLST => []   //  ?
        ,self::XML => ['xml']
        ,self::YAML => ['yml','yaml']
    ];


    /**
     * Static method for empiric loading a config instance.
     * @deprecated
     *
     * @param  string $path
     *
     * @return MultiConfig
     */
    public static function load($path)
    {
        return new static($path);
    }

    public static function loadFromFile($filename, $type=null)
    {

    }

    public static function loadFromString($string, $type=null)
    {

    }

    public static function loadFromArray(array $array)
    {

    }

    /**
     * Save the current configuration data into file
     *
     * @param type $filename
     * @param  string $type
     *
     * @return $this
     */
    public function save($filename, $type=null)
    {
        return $this;
    }


    /**
     * Loads a supported configuration file format.
     *
     * @param  mixed $data
     * @param  const $type
     *
     * @return $this
     *
     * @throws UnsupportedFormatException If `$type` is an unsupported format. or another error
     */
//     * @throws FileNotFoundException      If a file is not found at `$path`
    public function __construct($data=[], $type=static::ARR)
    {
        switch ($type) :
            case static::ARR :
                $this = $data;
                break;
            case static::CVS :
                throw new UnsupportedFormatException('Not yet');
                break;

            case static::INI :
                break;

            case static::PLST :
            case static::CFPL :
                break;

            case static::PHP :
                break;

            case static::XML :
                break;

            case static::YAML :
                break;

            default :
                throw new UnsupportedFormatException('Unsupported configuration format');
                break;
        endswitch;

        $this->_type = $type;


//        // Get file information
//        $info = pathinfo($path);
//
//        // Check if config file exists or throw an exception
//        if (!file_exists($path)) {
//            throw new FileNotFoundException("Configuration file: [$path] cannot be found");
//        }
//
//        // Check if a load-* method exists for the file extension, if not throw exception
//        $load_method = 'load' . ucfirst($info['extension']);
//        if (!method_exists(__CLASS__, $load_method)) {
//            throw new UnsupportedFormatException('Unsupported configuration format');
//        }
//
//        // Try and load file
//        $this->data = $this->$load_method($path);

        return $this;
    }

    /**
     * Loads a PHP file and gets its' contents as an array
     *
     * @param  string $path
     *
     * @return $this;
     *
     * @throws ParseException             If the PHP file throws an exception
     * @throws UnsupportedFormatException If the PHP file does not return an array
     */
    protected function loadPhp($path)
    {
        // Require the file, if it throws an exception, rethrow it
        try {
            $temp = require $path;
        }
        catch (\Exception $ex) {
            throw new ParseException(
                array(
                    'message'   => 'PHP file threw an exception',
                    'exception' => $ex
                )
            );
        }

        // If we have a callable, run it and expect an array back
        if (is_callable($temp)) {
            $temp = call_user_func($temp);
        }

        // Check for array, if its anything else, throw an exception
        if (!$temp || !is_array($temp)) {
            throw new UnsupportedFormatException('PHP file does not return an array');
        }

        return $this;
    }

    /**
     * Loads an INI file as an array
     *
     * @param  string $path
     *
     * @return $this;
     *
     * @throws ParseException If there is an error parsing the INI file
     */
    protected function loadIni($path)
    {
        $data = @parse_ini_file($path, true);

        if (!$data) {
            $error = error_get_last();
            throw new ParseException($error);
        }

        return $this;
    }

    /**
     * Loads a JSON file as an array
     *
     * @param  string $path
     *
     * @return $this;
     *
     * @throws ParseException If there is an error parsing the JSON file
     */
    protected function loadJson($path)
    {
        $data = json_decode(file_get_contents($path), true);

        if (function_exists('json_last_error_msg')) {
            $error_message = json_last_error_msg();
        } else {
            $error_message  = 'Syntax error';
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = array(
                'message' => $error_message,
                'type'    => json_last_error(),
                'file'    => $path
            );
            throw new ParseException($error);
        }

        return $this;
    }


    /**
     * Loads a XML file as an array
     *
     * @param  string $path
     *
     * @return $this;
     *
     * @throws ParseException If there is an error parsing the XML file
     */
    protected function loadXml($path)
    {
        libxml_use_internal_errors(true);

        $data = simplexml_load_file($path, null, LIBXML_NOERROR);

        if ($data === false) {
            $errors = libxml_get_errors();
            $latestError = array_pop($errors);
            $error = array(
                'message' => $latestError->message,
                'type'    => $latestError->level,
                'code'    => $latestError->code,
                'file'    => $latestError->file,
                'line'    => $latestError->line
            );
            throw new ParseException($error);
        }

        $data = json_decode(json_encode($data), true);

        return $this;
    }

    /**
     * Loads a YAML file as an array
     *
     * @param  string $path
     *
     * @return $this;
     *
     * @throws ParseException If If there is an error parsing the YAML file
     */
    protected function loadYaml($path)
    {
        try {
            $data = Yaml::parse($path);
        }
        catch(\Exception $ex) {
            throw new ParseException(
                array(
                    'message'   => 'Error parsing YAML file',
                    'exception' => $ex
                )
            );
        }

        return $this;
    }

    /**
     *
     * @param type $type
     *
     * @return string
     */
    public function getExtByType($type)
    {

    }

    /**
     * Gets a configuration setting using a simple or nested key.
     * Nested keys are similar to JSON paths that use the dot
     * dot notation.
     *
     * @also U can use directly access to value as an object property
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     * @throws KeyNotExistException     If a key is not found in the chain
     */
    public function get($key, $default = null) {

//        // Check if already cached
//        if (isset($this->cache[$key])) {
//            return $this->cache[$key];
//        }

        $segs = explode('.', $key);
        $root = $this->data;
        $k = [];

        // nested case
        foreach ($segs as $part) {
            $k[]=$part;
            if (isset($root[$part])){
                $root = $root[$part];
                continue;
            }
            else {
                throw new Exception\KeyNotExistException('Key ['.implode('.', $k).'] not exists');
                break;
            }
        }

        // whatever we have is what we needed
//        return ($this->cache[$key] = $root);
        return $root;
    }

    /**
     * Function for setting configuration values, using
     * either simple or nested keys.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return $this;
     */
    public function set($key, $value)
    {
        $segs = explode('.', $key);
        $root = &$this;

        // Look for the key, creating nested keys if needed
        while ($part = array_shift($segs)) {
            if (!isset($root[$part]) && count($segs)) {
                $root[$part] = array();
            }
            $root = &$root[$part];
        }

        // Assign value at target node
//        $this->cache[$key] = $root = $value;

        return $this;
    }

    /**
     * ArrayAccess Methods
     */

    /**
     * Gets a value using the offset as a key
     *
     * @param  string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Checks if a key exists
     *
     * @param  string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        $e = false;
        try {
            $this->get($offset);
        }
        catch (Exception $e) {}
        finally {
            return !$e;
        }
        //  return $this;
    }

    /**
     * Sets a value using the offset as a key
     *
     * @param  string $offset
     * @param  mixed  $value
     *
     * @return $this;
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
        return $this;
    }

    /**
     * Deletes a key and its value
     *
     * @param  string $offset
     *
     * @return $this;
     */
    public function offsetUnset($offset)
    {
        $segs = explode('.', $key);
        $root = &$this;

        while ($part = array_shift($segs) && count($segs) > 0) {
            $root = &$root[$part];
        }
        unset ($root[$part]);

        return $this;
    }


}
