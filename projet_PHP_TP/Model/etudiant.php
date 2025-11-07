<?php
class utilisateur
{
    private $id;
    private $matricule;
    public function __construct($matricule)
    {
        $this->matricule = $matricule;
    }
    public function __get($attr)
    {
        if (!isset($this->$attr))
            return "erreur";
        else
            return ($this->$attr);
    }
    public function __set($attr, $value)
    {
        $this->$attr = $value;
    }
    public function __toString()
    {
        $s = "";
        return $s;
    }
}
