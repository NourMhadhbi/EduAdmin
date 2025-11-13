<?php
class inscriptionCours
{
    private $etudiant_id;
    private $cours_id;
    public function __construct($etudiant_id, $cours_id)
    {
        $this->etudiant_id = $etudiant_id;
        $this->cours_id = $cours_id;
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
