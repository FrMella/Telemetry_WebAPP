<?php

/*
 * Logger error implementation, logging errors from webapp, no more than 256MB
 * Guarda errores producidos reportados por las rutinas, el tamano maximo son 256MB
 */

defined('Telemetry_ENGINE') or die('RESTRICTED ACCESS');

class AppLogger
{
    private $logfile = "";
    private $caller = "";
    private $logenabled = false;
    private $log_level = 2; // setea por defecto el nivel de advertencia
    public $stout = false;

    private $log_levels = array(
            1 =>'Information',
            2 =>'Warning', // por defecto
            3 =>'ERROR'
        );

    public function __construct($clientFileName)
    {
        global $settings;

        if (!$settings['log']['enabled']) {
            $this->logenabled = false;
        } else {
            $this->logfile = $settings['log']['location']."/AppLogger.log";
            if ($settings['log']['level']) {
                $this->log_level = $settings['log']['level'];
            }
            $this->caller = basename($clientFileName);
            if (!file_exists($this->logfile)) {
                $fh = @fopen($this->logfile, "a");
                @fclose($fh);
            }
            if (is_writable($this->logfile)) {
                $this->logenabled = true;
            }
        }
    }
    
    public function set($logfile, $log_level)
    {
        $this->logfile = $logfile;
        $this->log_enabled = true;
        $this->log_level = $log_level;
    }

    public function info($message)
    {
        if ($this->log_level <= 1) {
            $this->write("Information", $message);
        }
    }

    public function warn($message)
    {
        if ($this->log_level <= 2) {
            $this->write("Warning", $message);
        }
    }

    public function error($message)
    {
        if ($this->log_level <= 3) {
            $this->write("ERROR", $message);
        }
    }

    public function levels()
    {
        return $this->log_levels;
    }

    private function write($type, $message)
    {
        if (!$this->logenabled) {
            return;
        }
        
        if ($this->stout) {
            print $type." ".$message."\n";
        }

        $now = microtime(true);
        $micro = sprintf("%03d", ($now - ($now >> 0)) * 1000);
        $now = DateTime::createFromFormat('U', (int)$now);
        $now = $now->format("Y-m-d H:i:s").".$micro";
        if (filesize($this->logfile)>(1024*1024*256)) {
            $fh = @fopen($this->logfile, "w");
            @fclose($fh);
        }
        if ($fh = @fopen($this->logfile, "a")) {
            @fwrite($fh, $now."|$type|$this->caller|".$message."\n");
            @fclose($fh);
        }
    }
}
