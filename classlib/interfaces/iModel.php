<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * iModel is a MYSQL database interface, able to insert and delete.
 *
 * @author Halfdeck
 */
interface iModel {
    public function insert();
    public function delete();
}
?>
