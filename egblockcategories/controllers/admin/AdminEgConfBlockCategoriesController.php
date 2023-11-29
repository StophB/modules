<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBlockCategories
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

class AdminEgConfBlockCategoriesController extends ModuleAdminController
{
    public function initContent()
    {
        if (!$this->viewAccess()) {
            $this->errors[] = Tools::displayError('You do not have permission to view this.');
            return;
        }

        $idTab = (int) Tab::getIdFromClassName('AdminModules');
        $idEmployee = (int) $this->context->employee->id;
        $token = Tools::getAdminToken('AdminModules'.$idTab.$idEmployee);
        Tools::redirectAdmin('index.php?controller=AdminModules&configure=egblockcategories&token='.$token);
    }
}
