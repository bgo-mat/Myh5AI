<?php

class H5AI
{
    private $_tree;
    private $_path;

    public function __construct($path)
    {
        $this->_tree = [];
        $this->_path = $path;
        $this->buildTree($this->_path);
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getTree()
    {
        return $this->_tree;
    }

    private function buildTree($input, &$tabAllFile = null) {
        $directoryName = $input;
        if (is_null($tabAllFile)) {
            $tabAllFile = &$this->_tree;
        }

        $directories = array_filter(glob($directoryName . '/*'), 'is_dir');
        foreach ($directories as $dir) {
            $subTree = [];
            $this->buildTree($dir, $subTree);
            $tabAllFile[] = ['type' => 'directory', 'name' => basename($dir), 'path' => $dir, 'children' => $subTree];
        }

        $allFiles = array_filter(glob($directoryName . '/*'), 'is_file');
        foreach ($allFiles as $file) {
            $tabAllFile[] = ['type' => 'file', 'name' => basename($file), 'path' => $file];
        }
    }
}



