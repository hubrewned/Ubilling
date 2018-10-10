<?php

if (cfr('OMEGATV')) {
    $altCfg = $ubillingConfig->getAlter();
    if (@$altCfg['OMEGATV_ENABLED']) {
        $omega = new OmegaTV();
        show_window(__('OmegaTV'), $omega->renderPanel());

        //tariffs management
        if (wf_CheckGet(array('tariffs'))) {
            //creating new tariff
            if (wf_CheckPost(array('newtariffid'))) {
                $omega->createTariff();
                rcms_redirect($omega::URL_ME . '&tariffs=true');
            }

            //deleting existing tariff
            if (wf_CheckGet(array('deleteid'))) {
                $omega->deleteTariff($_GET['deleteid']);
                rcms_redirect($omega::URL_ME . '&tariffs=true');
            }

            //editing tariff
            if (wf_CheckPost(array('edittariffid'))) {
                $omega->catchTariffSave();
                rcms_redirect($omega::URL_ME . '&tariffs=true');
            }

            if (!wf_CheckGet(array('chanlist'))) {
                //listing available tariffs
                show_window(__('Tariffs'), $omega->renderTariffsList());
                //channels list preview
                show_window(__('Channels'), $omega->renderChanControls());
                //tariffs creation form
                show_window(__('Create new tariff'), $omega->renderTariffCreateForm());
            } else {
                //view tariff channels list
                show_window('', wf_BackLink($omega::URL_ME . '&tariffs=true'));
                show_window(__('Channels'), $omega->renderTariffsRemote($_GET['chanlist'], true, true));
            }
        }

        if (wf_CheckGet(array('subscriptions'))) {
            //getting new device activation code
            if (wf_CheckGet(array('getdevicecode'))) {
                die($omega->generateDeviceCode($_GET['getdevicecode']));
            }

            //deleting existing device for some user
            if (wf_CheckGet(array('deletedevice', 'customerid'))) {
                $deleteUniq = $_GET['deletedevice'];
                $deviceDeleteLogin = $omega->getLocalCustomerLogin($_GET['customerid']);
                $omega->deleteDevice($_GET['customerid'], $deleteUniq);
                log_register('OMEGATV DEVICE DELETE `' . $deleteUniq . '` FOR (' . $deviceDeleteLogin . ') AS [' . $_GET['customerid'] . ']');
                rcms_redirect($omega::URL_SUBSCRIBER . $_GET['customerid']);
            }

            //json ajax data for subscribers list
            if (wf_CheckGet(array('ajuserlist'))) {
                $omega->ajUserList();
            }

            //rendering user list container
            show_window(__('Subscriptions'), $omega->renderUserListContainer());
        }

        if (wf_CheckGet(array('customerprofile'))) {
            //user blocking
            if (wf_CheckGet(array('blockuser'))) {
                $omega->setCustomerActive($_GET['customerprofile'], false);
                rcms_redirect($omega::URL_SUBSCRIBER . $_GET['customerprofile']);
            }

            //user unblocking
            if (wf_CheckGet(array('unblockuser'))) {
                $omega->setCustomerActive($_GET['customerprofile'], true);
                rcms_redirect($omega::URL_SUBSCRIBER . $_GET['customerprofile']);
            }

            //user tariff editing
            if (wf_CheckPost(array('changebasetariff'))) {
                $omega->changeUserTariffs($_GET['customerprofile']);
                rcms_redirect($omega::URL_SUBSCRIBER . $_GET['customerprofile']);
            }

            show_window(__('Profile'), $omega->renderUserInfo($_GET['customerprofile']));
            show_window('', wf_BackLink($omega::URL_ME . '&subscriptions=true'));
        }

        if (wf_CheckGet(array('devices'))) {

            //deleting existing device
            if (wf_CheckGet(array('deletedevice', 'customerid'))) {
                $deleteUniq = $_GET['deletedevice'];
                $deviceDeleteLogin = $omega->getLocalCustomerLogin($_GET['customerid']);
                $omega->deleteDevice($_GET['customerid'], $deleteUniq);
                log_register('OMEGATV DEVICE DELETE `' . $deleteUniq . '` FOR (' . $deviceDeleteLogin . ') AS [' . $_GET['customerid'] . ']');
                rcms_redirect($omega::URL_ME . '&devices=true');
            }

            //rendering devices list
            show_window(__('Devices'), $omega->renderDevicesList());
        }
    } else {
        show_error(__('This module is disabled'));
    }
} else {
    show_error(__('Acccess denied'));
}
?>