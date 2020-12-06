<?php

//
// Includes
//

require_once(__DIR__ . '/errorbase.php');

//
// Types
//

class TwigWrapper extends ErrorBase
{
    //
    // Private data
    //

    /** @var string */ private $cache_folder;
    /** @var string */ private $template_folder;

    /** @var Twig_Loader_Filesystem */ private $twig_loader;
    /** @var Twig_Environment */       private $twig;
    /** @var string */                 private $root_template;

    //
    // Public routines
    //

    public function __construct()
    {
        parent::__construct();

        $this->cache_folder = '';
        $this->template_folder = '';

        $this->twig_loader = null;
        $this->twig = null;
        $this->root_template = '';
    }

    public function __destruct()
    {
        $this->close();
    }

    //
    // Configuration routines
    //

    public function setCacheFolder($val)
    {
        $this->cache_folder = $val;
    }

    public function getCacheFolder()
    {
        return $this->cache_folder;
    }

    public function setTemplateFolder($val)
    {
        $this->template_folder = $val;
    }

    public function getTemplateFolder()
    {
        return $this->template_folder;
    }

    //
    // Template system routines
    //

    /**
     * @return bool
     */
    public function open()
    {
        // Check settings
        if (strlen($this->cache_folder) == 0)
        {
            $this->addError(ErrorType::makeByText('Invalid cache folder.'));
            return false;
        }
        if (strlen($this->template_folder) == 0)
        {
            $this->addError(ErrorType::makeByText('Invalid template folder.'));
            return false;
        }

        // Make cache folder
        @mkdir($this->template_folder, 0777, true);

        // Initialize twig
        $twig_loader = new Twig_Loader_Filesystem($this->template_folder);

        $options = array();
        $options['auto_reload'] = true; // Force template recompile when template changes
        $options['cache'] = $this->cache_folder;
        $options['strict_variables'] = true; // Throws exception if accessed template variable wasn't defined
        $options['autoescape'] = true;

        $twig = new Twig_Environment($twig_loader, $options);

        $this->twig_loader = $twig_loader;
        $this->twig = $twig;

        return true;
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        return ($this->twig !== null);
    }

    public function close()
    {
        $this->twig_loader = null;
        $this->twig = null;
    }

    /**
     * @param string $template_file
     * @return bool
     */
    public function setRootTemplate($template_file)
    {
        if (!$this->isOpen())
        {
            return false;
        }

        $this->root_template = $template_file;
        return true;
    }

    /**
     * @param string $key
     * @param mixed $val
     * @return bool
     */
    public function setVar($key, $val)
    {
        if (!$this->isOpen())
        {
            return false;
        }

        $this->twig->addGlobal($key, $val);
        return true;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (!$this->isOpen())
        {
            return '';
        }
        if (strlen($this->root_template) == 0)
        {
            return '';
        }

        return $this->twig->render($this->root_template);
    }

    // ...
}