<?php

class My_View_Helper_FormatearFecha extends Zend_View_Helper_Abstract
{

    public function formatearFecha($fecha_sql, $solo_fecha = true)
    {

        $date = new DateTime($fecha_sql);

        if ($solo_fecha) {
            return $date->format('d/m/Y');
        } else {
            return $date->format('d/m/Y H:i:s');
        }


    }
}