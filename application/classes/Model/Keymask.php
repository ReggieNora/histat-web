<?php

defined('SYSPATH') or die('No direct access allowed.');

class Model_Keymask extends ORM {

    protected $_table_name = 'Aka_Schluesselmaske';
    protected $_table_columns = array(
        'ID_HS' => array(),
        'Name' => array(),
        'ID_Projekt' => array(),
        'chdate' => array()
    );
    protected $_primary_key = 'ID_HS';
    protected $_belongs_to = array('project' => array('model' => 'Project', 'foreign_key' => 'ID_Projekt', 'far_key' => 'ID_Projekt'));
    protected $_has_many = array(
        'literatures' => array('model' => 'Literature', 'foreign_key' => 'ID_HS', 'far_key' => 'ID_HS'),
        'keycodes' => array('model' => 'Keycode', 'foreign_key' => 'ID_HS', 'far_key' => 'ID_HS'),
        'timelines' => array('model' => 'Timeline', 'foreign_key' => 'ID_HS', 'far_key' => 'ID_HS'),
    );

    public function getDetails($filter) {
        $details = DB::select("k.ID_CodeKuerz", "Position", "CodeBeschreibung", "Zeichen", "disabled", "Code", "CodeBezeichnung")
                        ->distinct(true)
                        ->from(array("Aka_SchluesselCode", "k"))
                        ->join(array('Aka_Codes', 'c'), 'LEFT')
                        ->on('k.ID_CodeKuerz', '=', 'c.ID_CodeKuerz')
                        ->join(array('Aka_CodeInhalt', 'ci'), 'LEFT')
                        ->on('ci.ID_CodeKuerz', '=', 'c.ID_CodeKuerz')
                        ->where("k.ID_HS", "=", $this->ID_HS)
                        ->order_by('Position')->order_by('Code')->as_object()->execute();
        $result = array();
  
        $keys = $this->getKeys($filter);

        foreach ($keys as $key) {
            foreach ($details as $detail) {
                if ($detail->Code === substr($key->key, $detail->Position - 1, $detail->Zeichen)) {
                    $result['details'][$detail->ID_CodeKuerz][$key->key] = $detail;
                    $result['titles'][$key->key][] = $detail->CodeBeschreibung . ': ' . $detail->CodeBezeichnung;
                    $result['filters'][$detail->ID_CodeKuerz][$detail->Code . '_' . $detail->Position . '_' . $detail->Zeichen] = $detail->CodeBezeichnung;
                    $result['keys'][$key->key] = $key->key;
                    $result['tables'][$key->key] = $key->Tabelle;
                    $result['sources'][$key->key] = $key->Quelle;
                    $result['notes'][$key->key] = $key->Anmerkung;
                }
            }
        }
        
        
        return $result;
    }
  public function getKeys($filter) {
        $result = DB::select(array("Schluessel", "key"), "Tabelle", "Quelle", "Anmerkung")->distinct(true)
                ->from("Lit_ZR")
                ->where('ID_HS', '=', $this->ID_HS)
                ->where("Schluessel", "LIKE", $filter);

        return $result->as_object()->execute();
    }
    public function getData($filter) {

        $rows = DB::select("Data", "Jahr_Sem", "Schluessel", "Anmerkung")
                ->distinct(true)
                ->from("Daten__Aka")
                ->where("ID_HS", "=", $this->ID_HS)
                ->where("Schluessel", "LIKE", $filter)
                //->where(DB::expr("CAST(Jahr_Sem AS DECIMAL)"), "BETWEEN", DB::expr("1871 AND 1874"))
                ->order_by("Jahr_Sem")
                
                ->execute();
        $result = array();
   
        foreach ($rows as $row) {
          
            $result[$row['Jahr_Sem']][$row['Schluessel']] = array('data' => $row['Data'], 'note' => Arr::get($row, 'Anmerkung'));
         
            
        }
       
        return $result;
    }

  

}
