<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package    con4gis
 * @version    7
 * @author     con4gis contributors (see "authors.txt")
 * @license    LGPL-3.0-or-later
 * @copyright  Küstenschmiede GmbH Software & Design
 * @link       https://www.con4gis.org
 */

namespace con4gis\ReservationBundle\Resources\contao\modules;

use con4gis\CoreBundle\Classes\Helper\InputHelper;
use con4gis\CoreBundle\Resources\contao\models\C4gLogModel;
use con4gis\ProjectsBundle\Classes\Actions\C4GSaveAndRedirectDialogAction;
use con4gis\ProjectsBundle\Classes\Buttons\C4GBrickButton;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickCommon;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickConst;
use con4gis\ProjectsBundle\Classes\Common\C4GBrickRegEx;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickCondition;
use con4gis\ProjectsBundle\Classes\Conditions\C4GBrickConditionType;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GButtonField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GDateField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GEmailField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GForeignKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GHeadlineField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GKeyField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GLabelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GMultiCheckboxField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GNumberField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GPostalField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GRadioGroupField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSelectField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GSubDialogField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTelField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextareaField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTextField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimeField;
use con4gis\ProjectsBundle\Classes\Fieldtypes\C4GTimepickerField;
use con4gis\ProjectsBundle\Classes\Framework\C4GBrickModuleParent;
use con4gis\ProjectsBundle\Classes\Views\C4GBrickViewType;
use con4gis\ReservationBundle\Classes\C4gReservationBrickTypes;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventAudienceModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventSpeakerModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationEventTopicModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationLocationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationObjectModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationParamsModel;
use con4gis\ReservationBundle\Resources\contao\models\C4gReservationTypeModel;
use Contao\Date;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class C4gReservation extends C4GBrickModuleParent
{
    protected $tableName    = 'tl_c4g_reservation';
    protected $modelClass   = C4gReservationModel::class;
    protected $languageFile = 'fe_c4g_reservation';
    protected $brickKey     = C4gReservationBrickTypes::BRICK_RESERVATION;
    protected $viewType     = C4GBrickViewType::PUBLICFORM;
    protected $sendEMails   = null;
    protected $brickScript  = 'bundles/con4gisreservation/js/c4g_brick_reservation.js';
    protected $brickStyle   = 'bundles/con4gisreservation/css/c4g_brick_reservation.css';
    protected $strTemplate  = 'mod_c4g_brick_simple';
    protected $withNotification = true;

    protected $jQueryUseTable = false;
    protected $jQueryUseScrollPane = false;
    protected $jQueryUsePopups = false;
    protected $loadChosenResources = false;

    public function initBrickModule($id)
    {
        parent::initBrickModule($id);

        $this->dialogParams->setWithoutGuiHeader(true);

        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_SAVE_AND_NEW);
        $this->dialogParams->deleteButton(C4GBrickConst::BUTTON_DELETE);
        $this->dialogParams->setWithoutGuiHeader(true);
        $this->dialogParams->setRedirectSite($this->reservation_redirect_site);
        $this->dialogParams->setSaveWithoutSavingMessage(true);
        $this->brickCaption = $GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption'];
        $this->brickCaptionPlural = $GLOBALS['TL_LANG']['fe_c4g_reservation']['brick_caption_plural'];
    }


    public function addFields()
    {
        $fieldList = array();

        $idField = new C4GKeyField();
        $idField->setFieldName('id');
        $idField->setEditable(false);
        $idField->setFormField(false);
        $idField->setSortColumn(false);
        $fieldList[] = $idField;

        $typelist = array();

        $eventId  = $this->Input->get('event') ? $this->Input->get('event') : 0;
        $event    = $eventId ? \CalendarEventsModel::findByPk($eventId) : false;
        $eventObj = $event && $event->published ? C4gReservationEventModel::findOneBy('pid', $event->id) : false;
        if ($eventObj) {
            $typeId = $eventObj->reservationType;
            $types[] = C4gReservationTypeModel::findByPk($typeId); //ToDo check published
        } else {
            $types = C4gReservationTypeModel::findBy('published', '1');
        }

        if ($types) {
            $moduleTypes = unserialize($this->reservation_types);
            foreach ($types as $type) {
                if ($moduleTypes && (count($moduleTypes) > 0)) {
                    $arrModuleTypes = $moduleTypes;
                    if (!in_array($type->id, $arrModuleTypes)) {
                        continue;
                    }
                }

                $objects = C4gReservationObjectModel::getReservationObjectList(array($type->id), $eventId);
                if (!$objects || (count($objects) <= 0)) {
                    continue;
                }

                $captions = unserialize($type->options);
                if ($captions) {
                    foreach ($captions as $caption) {
                        if ($caption['language'] == $GLOBALS['TL_LANGUAGE']) {
                            $typelist[$type->id] = array(
                                'id' => $type->id,
                                'name' => $caption['caption'] ? $caption['caption'] : $type->caption,
                                'periodType' => $type->periodType,
                                'includedParams' => unserialize($type->included_params),
                                'additionalParams' => unserialize($type->additional_params),
                                'objects' => $objects,
                                'isEvent' => $type->reservationObjectType && $type->reservationObjectType === '2' ? true : false
                            );
                        }
                    }
                }
            }
        }

        if (count($typelist) > 0) {
            $firstType = array_key_first($typelist);

            $onLoadScript = $this->getDialogParams()->getOnloadScript();
            $onLoadScript .= ' jQuery("#c4g_reservation_type").trigger("change");';
            $this->getDialogParams()->setOnloadScript(trim($onLoadScript));

            $reservationTypeField = new C4GSelectField();
            $reservationTypeField->setChosen(false);
            $reservationTypeField->setFieldName('reservation_type');
            $reservationTypeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_type']);
            $reservationTypeField->setSortColumn(false);
            $reservationTypeField->setTableColumn(false);
            $reservationTypeField->setColumnWidth(20);
            $reservationTypeField->setSize(1);
            $reservationTypeField->setOptions($typelist);
            $reservationTypeField->setMandatory(true);
            $reservationTypeField->setCallOnChange(true);
            $reservationTypeField->setCallOnChangeFunction("setReservationForm(this, " . $this->id . ", -1 ,'getCurrentTimeset')");
            $reservationTypeField->setInitialValue($firstType);
            $reservationTypeField->setStyleClass('reservation-type');
            $reservationTypeField->setHidden(count($typelist) == 1);
            $fieldList[] = $reservationTypeField;
        }

        foreach ($typelist as $type) {
            $isEvent = $type['isEvent'];
            $reservationObjects = $type['objects'];

            $condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_type', $type['id']);

            if ($this->withCapacity) {
                $conditionCapacity = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'desiredCapacity_'.$type['id']);
                $reservationDesiredCapacity = new C4GNumberField();
                $reservationDesiredCapacity->setFieldName('desiredCapacity');
                $reservationDesiredCapacity->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['desiredCapacity']);
                $reservationDesiredCapacity->setFormField(true);
                $reservationDesiredCapacity->setEditable(true);
                $reservationDesiredCapacity->setCondition(array($condition));
                $reservationDesiredCapacity->setInitialValue(1);
                $reservationDesiredCapacity->setMandatory(true);
                $reservationDesiredCapacity->setMin(1);
                $reservationDesiredCapacity->setPattern(C4GBrickRegEx::NUMBERS);
                $reservationDesiredCapacity->setCallOnChange(true);
                if ($isEvent) {
                    //ToDo
                    //$reservationDesiredCapacity->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_".$type['id']."')," . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                } else {
                    $reservationDesiredCapacity->setCallOnChangeFunction("setTimeset(document.getElementById('c4g_beginDate_".$type['id']."')," . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                }
                $reservationDesiredCapacity->setNotificationField(true);
                $reservationDesiredCapacity->setAdditionalID($type['id']);
                $reservationDesiredCapacity->setStyleClass('desired-capacity');
                //$reservationDesiredCapacity->setHidden(!$this->withCapacity);
                $fieldList[] = $reservationDesiredCapacity;
            }


            //Default fields
            if (!$isEvent) {
                //set reservationObjectType to default
                $reservationObjectTypeField = new C4GNumberField();
                $reservationObjectTypeField->setFieldName('reservationObjectType');
                $reservationObjectTypeField->setInitialValue('1');
                $reservationObjectTypeField->setDatabaseField(true);
                $reservationObjectTypeField->setFormField(false);
                $fieldList[] = $reservationObjectTypeField;

                $additionalDuration = StringUtil::deserialize($this->additionalDuration);
                if ($additionalDuration == "1") {
                    $durationField = new C4GNumberField();
                    $durationField->setFieldName('duration');
                    $durationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['duration']);
                    $durationField->setColumnWidth(10);
                    $durationField->setFormField(true);
                    $durationField->setSortColumn(true);
                    $durationField->setTableColumn(true);
                    $durationField->setMandatory(true);
                    $durationField->setCallOnChange(true);
                    $durationField->setCallOnChangeFunction("setTimeset(this, " . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                    $durationField->setCondition(array($condition));
                    $durationField->setNotificationField(true);
                    $durationField->setStyleClass('duration');
                    $fieldList[] = $durationField;
                } else {
                    $additionalDuration = 0;
                }

                if (($type['periodType'] === 'minute') || ($type['periodType'] === 'hour')) {
                    //$conditionDate = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'beginDate_'.$type['id']);

                    $reservationBeginDateField = new C4GDateField();
                    $reservationBeginDateField->setMinDate(C4gReservationObjectModel::getMinDate($reservationObjects));
                    $reservationBeginDateField->setMaxDate(C4gReservationObjectModel::getMaxDate($reservationObjects));
                    $reservationBeginDateField->setExcludeWeekdays(C4gReservationObjectModel::getWeekdayExclusionString($reservationObjects));
                    $reservationBeginDateField->setExcludeDates(C4gReservationObjectModel::getDateExclusionString($reservationObjects, $type));
                    $reservationBeginDateField->setFieldName('beginDate');
                    $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDate']);
                    $reservationBeginDateField->setEditable(true);
                    //$reservationBeginDateField->setInitialValue(C4gReservationObjectModel::getMinDate($reservationObjects));
                    $reservationBeginDateField->setComparable(false);
                    $reservationBeginDateField->setSortColumn(true);
                    $reservationBeginDateField->setSortSequence('de_datetime');
                    $reservationBeginDateField->setTableColumn(true);
                    $reservationBeginDateField->setFormField(true);
                    $reservationBeginDateField->setColumnWidth(10);
                    $reservationBeginDateField->setMandatory(true);
                    $reservationBeginDateField->setCondition(array($condition));
                    $reservationBeginDateField->setCallOnChange(true);
                    $reservationBeginDateField->setCallOnChangeFunction("setTimeset(this, " . $this->id . "," . $type['id'] . ",'getCurrentTimeset');");
                    $reservationBeginDateField->setNotificationField(true);
                    $reservationBeginDateField->setAdditionalID($type['id']);
                    $reservationBeginDateField->setStyleClass('begin-date');
                    $fieldList[] = $reservationBeginDateField;
                }

                $reservationendTimeField = new C4GTextField();
                $reservationendTimeField->setFieldName('endTime');
                $reservationendTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endTime']);
                $reservationendTimeField->setFormField(false);
                $reservationendTimeField->setHidden(true);
                $reservationendTimeField->setEditable(true);
                $reservationendTimeField->setSort(false);
                $reservationendTimeField->setDatabaseField(true);
                $reservationendTimeField->setCallOnChange(true);
                $reservationendTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                $reservationendTimeField->setNotificationField(true);
                $reservationendTimeField->setRemoveWithEmptyCondition(true);
                $reservationendTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                $fieldList[] = $reservationendTimeField;

                if (($type['periodType'] === 'hour') || ($type['periodType'] === 'minute')) {
                    $su_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $su_condition->setModel(C4gReservationObjectModel::class);
                    $su_condition->setFunction('isSunday');
                    $suConditionArr = [$su_condition,$condition];

                    $suReservationTimeField = new C4GRadioGroupField();
                    $suReservationTimeField->setFieldName('beginTime');
                    $suReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $suReservationTimeField->setFormField(true);
                    $suReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'su',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 0)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $suReservationTimeField->setMandatory(true);
                    $suReservationTimeField->setInitInvisible(true);
                    $suReservationTimeField->setSort(false);
                    $suReservationTimeField->setCondition($suConditionArr);
                    $suReservationTimeField->setCallOnChange(true);
                    $suReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $suReservationTimeField->setAdditionalID($type['id'].'-000');
                    $suReservationTimeField->setNotificationField(true);
                    $suReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $suReservationTimeField->setTurnButton(true);
                    $suReservationTimeField->setRemoveWithEmptyCondition(true);
                    $suReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $suReservationTimeField;

                    $mo_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $mo_condition->setModel(C4gReservationObjectModel::class);
                    $mo_condition->setFunction('isMonday');
                    $moConditionArr = [$mo_condition,$condition];

                    $moReservationTimeField = new C4GRadioGroupField();
                    $moReservationTimeField->setFieldName('beginTime');
                    $moReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $moReservationTimeField->setFormField(true);
                    $moReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'mo',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 1)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $moReservationTimeField->setMandatory(true);
                    $moReservationTimeField->setInitInvisible(true);
                    $moReservationTimeField->setSort(false);
                    $moReservationTimeField->setCondition($moConditionArr);
                    $moReservationTimeField->setCallOnChange(true);
                    $moReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $moReservationTimeField->setAdditionalID($type['id'].'-001');
                    $moReservationTimeField->setNotificationField(true);
                    $moReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $moReservationTimeField->setTurnButton(true);
                    $moReservationTimeField->setRemoveWithEmptyCondition(true);
                    $moReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $moReservationTimeField;

                    $tu_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $tu_condition->setModel(C4gReservationObjectModel::class);
                    $tu_condition->setFunction('isTuesday');
                    $tuConditionArr = [$tu_condition,$condition];

                    $tuReservationTimeField = new C4GRadioGroupField();
                    $tuReservationTimeField->setFieldName('beginTime');
                    $tuReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $tuReservationTimeField->setFormField(true);
                    $tuReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'tu',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 2)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $tuReservationTimeField->setMandatory(true);
                    $tuReservationTimeField->setInitInvisible(true);
                    $tuReservationTimeField->setSort(false);
                    $tuReservationTimeField->setCondition($tuConditionArr);
                    $tuReservationTimeField->setCallOnChange(true);
                    $tuReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $tuReservationTimeField->setAdditionalID($type['id'].'-002');
                    $tuReservationTimeField->setNotificationField(true);
                    $tuReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $tuReservationTimeField->setTurnButton(true);
                    $tuReservationTimeField->setRemoveWithEmptyCondition(true);
                    $tuReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $tuReservationTimeField;

                    $we_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $we_condition->setModel(C4gReservationObjectModel::class);
                    $we_condition->setFunction('isWednesday');
                    $weConditionArr = [$we_condition,$condition];

                    $weReservationTimeField = new C4GRadioGroupField();
                    $weReservationTimeField->setFieldName('beginTime');
                    $weReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $weReservationTimeField->setFormField(true);
                    $weReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'we',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 3)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $weReservationTimeField->setMandatory(true);
                    $weReservationTimeField->setInitInvisible(true);
                    $weReservationTimeField->setSort(false);
                    $weReservationTimeField->setCondition($weConditionArr);
                    $weReservationTimeField->setCallOnChange(true);
                    $weReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $weReservationTimeField->setAdditionalID($type['id'].'-003');
                    $weReservationTimeField->setNotificationField(true);
                    $weReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $weReservationTimeField->setTurnButton(true);
                    $weReservationTimeField->setRemoveWithEmptyCondition(true);
                    $weReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $weReservationTimeField;

                    $th_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $th_condition->setModel(C4gReservationObjectModel::class);
                    $th_condition->setFunction('isThursday');
                    $thConditionArr = [$th_condition,$condition];

                    $thReservationTimeField = new C4GRadioGroupField();
                    $thReservationTimeField->setFieldName('beginTime');
                    $thReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $thReservationTimeField->setFormField(true);
                    $thReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'th',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 4)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $thReservationTimeField->setMandatory(true);
                    $thReservationTimeField->setInitInvisible(true);
                    $thReservationTimeField->setSort(false);
                    $thReservationTimeField->setCondition($thConditionArr);
                    $thReservationTimeField->setCallOnChange(true);
                    $thReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $thReservationTimeField->setAdditionalID($type['id'].'-004');
                    $thReservationTimeField->setNotificationField(true);
                    $thReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $thReservationTimeField->setTurnButton(true);
                    $thReservationTimeField->setRemoveWithEmptyCondition(true);
                    $thReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $thReservationTimeField;

                    $fr_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $fr_condition->setModel(C4gReservationObjectModel::class);
                    $fr_condition->setFunction('isFriday');
                    $frConditionArr = [$fr_condition,$condition];

                    $frReservationTimeField = new C4GRadioGroupField();
                    $frReservationTimeField->setFieldName('beginTime');
                    $frReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $frReservationTimeField->setFormField(true);
                    $frReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'fr',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 5)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $frReservationTimeField->setMandatory(true);
                    $frReservationTimeField->setInitInvisible(true);
                    $frReservationTimeField->setSort(false);
                    $frReservationTimeField->setCondition($frConditionArr);
                    $frReservationTimeField->setCallOnChange(true);
                    $frReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $frReservationTimeField->setAdditionalID($type['id'].'-005');
                    $frReservationTimeField->setNotificationField(true);
                    $frReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $frReservationTimeField->setTurnButton(true);
                    $frReservationTimeField->setRemoveWithEmptyCondition(true);
                    $frReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $frReservationTimeField;

                    $sa_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'beginDate_' . $type['id']);
                    $sa_condition->setModel(C4gReservationObjectModel::class);
                    $sa_condition->setFunction('isSaturday');
                    $saConditionArr = [$sa_condition,$condition];

                    $saReservationTimeField = new C4GRadioGroupField();
                    $saReservationTimeField->setFieldName('beginTime');
                    $saReservationTimeField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $saReservationTimeField->setFormField(true);
                    $saReservationTimeField->setEditable(false);
                    $saReservationTimeField->setOptions(
                        C4gReservationObjectModel::getReservationTimes(
                            $reservationObjects,
                            $type['id'],
                            'sa',
                            date($GLOBALS['TL_CONFIG']['dateFormat'], C4gReservationObjectModel::getNextWeekday($reservationObjects, 6)),
                            $additionalDuration,
                            $this->showEndTime,
                            $this->showFreeSeats
                        ));
                    $saReservationTimeField->setMandatory(true);
                    $saReservationTimeField->setInitInvisible(true);
                    $saReservationTimeField->setSort(false);
                    $saReservationTimeField->setCondition($saConditionArr);
                    $saReservationTimeField->setCallOnChange(true);
                    $saReservationTimeField->setCallOnChangeFunction('setObjectId(this,'.$type['id'].')');
                    $saReservationTimeField->setAdditionalID($type['id'].'-006');
                    $saReservationTimeField->setNotificationField(true);
                    $saReservationTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $saReservationTimeField->setTurnButton(true);
                    $saReservationTimeField->setRemoveWithEmptyCondition(true);
                    $saReservationTimeField->setStyleClass('reservation_time_button reservation_time_button_'.$type['id']);
                    $fieldList[] = $saReservationTimeField;

                }

                $objects = [];
                foreach ($reservationObjects as $reservationObject) {

                    //ToDo Check Capacity
                    $objects[] = array(
                        'id' => $reservationObject->getId(),
                        'name' => $reservationObject->getCaption(),
                        'min' => $reservationObject->getDesiredCapacity()[0] ? $reservationObject->getDesiredCapacity()[0] : 1,
                        'max' => $reservationObject->getDesiredCapacity()[1] ? $reservationObject->getDesiredCapacity()[1] : 1
                    );
                }
            } else { //event
                //set reservationObjectType to event
                $reservationObjectTypeDBField = new C4GNumberField();
                $reservationObjectTypeDBField->setFieldName('reservationObjectType');
                $reservationObjectTypeDBField->setInitialValue('2');
                $reservationObjectTypeDBField->setDatabaseField(true);
                $reservationObjectTypeDBField->setFormField(false);
                $fieldList[] = $reservationObjectTypeDBField;

                $objects = [];
                foreach ($reservationObjects as $reservationObject) {

                    //ToDo Check Capacity
                    $objects[] = array(
                        'id' => $reservationObject->getId(),
                        'name' => $reservationObject->getNumber() ? '['.$reservationObject->getNumber().']&nbsp;'.$reservationObject->getCaption() : $reservationObject->getCaption(),
                        'min' => $reservationObject->getDesiredCapacity()[0] ? $reservationObject->getDesiredCapacity()[0] : 1,
                        'max' => $reservationObject->getDesiredCapacity()[1] ? $reservationObject->getDesiredCapacity()[1] : 1
                    );
                }

                //save event id as reservation object
                $reservationObjectDBField = new C4GNumberField();
                $reservationObjectDBField->setFieldName('reservation_object');
                $reservationObjectDBField->setDatabaseField(true);
                $reservationObjectDBField->setFormField(false);
                $fieldList[] = $reservationObjectDBField;

                //save beginDate
                $reservationBeginDateDBField = new C4GNumberField();
                $reservationBeginDateDBField->setFieldName('beginDate');
                $reservationBeginDateDBField->setInitialValue(0);
                $reservationBeginDateDBField->setDatabaseField(true);
                $reservationBeginDateDBField->setFormField(false);
                $reservationBeginDateDBField->setMax(999999999999);
                $fieldList[] = $reservationBeginDateDBField;

                //save beginTime
                $reservationBeginTimeDBField = new C4GNumberField();
                $reservationBeginTimeDBField->setFieldName('beginTime');
                $reservationBeginTimeDBField->setInitialValue(0);
                $reservationBeginTimeDBField->setDatabaseField(true);
                $reservationBeginTimeDBField->setFormField(false);
                $reservationBeginTimeDBField->setMax(999999999999);
                $fieldList[] = $reservationBeginTimeDBField;
            }

            //save endDate
            $reservationEndDateDBField = new C4GNumberField();
            $reservationEndDateDBField->setFieldName('endDate');
            $reservationEndDateDBField->setInitialValue(0);
            $reservationEndDateDBField->setDatabaseField(true);
            $reservationEndDateDBField->setFormField(false);
            $reservationEndDateDBField->setMax(9999999999999);
            $fieldList[] = $reservationEndDateDBField;

            //save endTime
            $reservationEndTimeDBField = new C4GNumberField();
            $reservationEndTimeDBField->setFieldName('endTime');
            $reservationEndTimeDBField->setInitialValue(0);
            $reservationEndTimeDBField->setDatabaseField(true);
            $reservationEndTimeDBField->setFormField(false);
            $reservationEndTimeDBField->setMax(9999999999999);
            $fieldList[] = $reservationEndTimeDBField;

            $reservationObjectField = new C4GSelectField();
            $reservationObjectField->setChosen(false);
            $reservationObjectField->setFieldName($isEvent ? 'reservation_object_event' : 'reservation_object');
            $reservationObjectField->setTitle($isEvent ? $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_event'] : $GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object']);
            $reservationObjectField->setDescription($isEvent ? '' : $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_object']);
            $reservationObjectField->setFormField(true);
            $reservationObjectField->setEditable($isEvent && !$eventId);
            $reservationObjectField->setOptions($objects);
            $reservationObjectField->setMandatory(true);
            $reservationObjectField->setNotificationField(true);
            $reservationObjectField->setRangeField('desiredCapacity_' . $type['id']);
            $reservationObjectField->setStyleClass($isEvent ? 'reservation-event-object displayReservationObjects' : 'reservation-object displayReservationObjects');
            $reservationObjectField->setWithEmptyOption(!$isEvent); //ToDo
            $reservationObjectField->setShowIfEmpty(true); //ToDo
            $reservationObjectField->setDatabaseField(!$isEvent);
            $reservationObjectField->setEmptyOptionLabel($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_object_none']);
            $reservationObjectField->setCondition([$condition]);
            $reservationObjectField->setRemoveWithEmptyCondition(true);
            $reservationObjectField->setCallOnChange($isEvent);
            $reservationObjectField->setCallOnChangeFunction("checkEventFields(this)");
            $reservationObjectField->setAdditionalID($type["id"]);
            $fieldList[] = $reservationObjectField;

            if ($isEvent) {
                foreach ($reservationObjects as $reservationObject) {
                    $val_condition = new C4GBrickCondition(C4GBrickConditionType::VALUESWITCH, 'reservation_object_event_' . $type['id'], $reservationObject->getId());
                    $obj_condition = new C4GBrickCondition(C4GBrickConditionType::METHODSWITCH, 'reservation_object_event_' . $type['id']);
                    $obj_condition->setModel(C4gReservationObjectModel::class);
                    $obj_condition->setFunction('isEventObject');
                    $objConditionArr = [$obj_condition,$val_condition];

                    $reservationBeginDateField = new C4gDateField();
                    $reservationBeginDateField->setFieldName('beginDateEvent');
                    $reservationBeginDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationBeginDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationBeginDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginDateEvent']);
                    $reservationBeginDateField->setEditable(false);
                    $reservationBeginDateField->setComparable(false);
                    $reservationBeginDateField->setDatabaseField(false);
                    $reservationBeginDateField->setSortColumn(true);
                    $reservationBeginDateField->setSortSequence('de_datetime');
                    $reservationBeginDateField->setTableColumn(false);
                    $reservationBeginDateField->setFormField(true);
                    $reservationBeginDateField->setColumnWidth(10);
                    $reservationBeginDateField->setMandatory(false);
                    $reservationBeginDateField->setCondition($objConditionArr);
                    $reservationBeginDateField->setRemoveWithEmptyCondition(true);
                    $reservationBeginDateField->setInitialValue($reservationObject->getBeginDate());
                    $reservationBeginDateField->setNotificationField(true);
                    $reservationBeginDateField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                    $reservationBeginDateField->setStyleClass('begindate-event');
                    $fieldList[] = $reservationBeginDateField;

                    $reservationEndDateField = new C4GDateField();
                    $reservationEndDateField->setFieldName('endDateEvent');
                    $reservationEndDateField->setCustomFormat($GLOBALS['TL_CONFIG']['dateFormat']);
                    $reservationEndDateField->setCustomLanguage($GLOBALS['TL_LANGUAGE']);
                    $reservationEndDateField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['endDateEvent']);
                    $reservationEndDateField->setEditable(false);
                    $reservationEndDateField->setComparable(false);
                    $reservationEndDateField->setSortColumn(true);
                    $reservationEndDateField->setSortSequence('de_datetime');
                    $reservationEndDateField->setDatabaseField(false);
                    $reservationEndDateField->setTableColumn(false);
                    $reservationEndDateField->setFormField(true);
                    $reservationEndDateField->setColumnWidth(10);
                    $reservationEndDateField->setMandatory(false);
                    $reservationEndDateField->setCondition($objConditionArr);
                    $reservationEndDateField->setRemoveWithEmptyCondition(true);
                    $reservationEndDateField->setInitialValue($reservationObject->getEndDate());
                    $reservationEndDateField->setNotificationField(true);
                    $reservationEndDateField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                    $reservationEndDateField->setShowIfEmpty(false);
                    $reservationEndDateField->setStyleClass('enddate-event');
                    $fieldList[] = $reservationEndDateField;

                    $reservationBeginTimeField = new C4GRadioGroupField();
                    $reservationBeginTimeField->setFieldName('beginTimeEvent');
                    $reservationBeginTimeField->setTitle($isEvent ? $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeEvent'] : $GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTime']);
                    $reservationBeginTimeField->setFormField(true);
                    $reservationBeginTimeField->setOptions(C4gReservationObjectModel::getReservationEventTime($reservationObject, $this->showEndTime, $this->showFreeSeats));
                    $reservationBeginTimeField->setMandatory(false);
                    $reservationBeginTimeField->setInitialValue($reservationObject->getBeginTime());
                    $reservationBeginTimeField->setDatabaseField(false);
                    $reservationBeginTimeField->setSort(false);
                    $reservationBeginTimeField->setCondition($objConditionArr);
                    $reservationBeginTimeField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                    $reservationBeginTimeField->setNotificationField(true);
                    $reservationBeginTimeField->setClearGroupText($GLOBALS['TL_LANG']['fe_c4g_reservation']['beginTimeClearGroupText']);
                    $reservationBeginTimeField->setTurnButton(true);
                    $reservationBeginTimeField->setRemoveWithEmptyCondition(true);
                    $reservationBeginTimeField->setStyleClass('reservation_time_event_button reservation_time_event_button_'.$type['id'].'-22'.$reservationObject->getId().C4gReservationObjectModel::getButtonStateClass($reservationObject));
                    $fieldList[] = $reservationBeginTimeField;

                    $locationId = $reservationObject->getLocation();
                    if ($locationId) {
                        $location = C4gReservationLocationModel::findByPk($locationId);
                        if ($location) {
                            $locationName = $location->name;
                            $street = $location->contact_street;
                            $postal = $location->contact_postal;
                            $city = $location->contact_city;
                            if ($street && $postal && $city) {
                                $locationName.= "&nbsp;(".$street.",&nbsp;".$postal."&nbsp;".$city.")";
                            }
                            $reservationLocationField = new C4GTextField();
                            $reservationLocationField->setFieldName('location');
                            $reservationLocationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['eventlocation']);
                            $reservationLocationField->setFormField(true);
                            $reservationLocationField->setEditable(false);
                            $reservationLocationField->setDatabaseField(false);
                            $reservationLocationField->setCondition($objConditionArr);
                            $reservationLocationField->setInitialValue($locationName);
                            $reservationLocationField->setMandatory(false);
                            $reservationLocationField->setShowIfEmpty(false);
                            $reservationLocationField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                            $reservationLocationField->setRemoveWithEmptyCondition(true);
                            $reservationLocationField->setNotificationField(true);
                            $reservationLocationField->setSimpleTextWithoutEditing(true);
                            $reservationLocationField->setStyleClass('eventdata eventdata_'.$type['id'].'-22'.$reservationObject->getId().' event-location');
                            $fieldList[] = $reservationLocationField;
                        }
                    }

                    $speakerIds = $reservationObject->getSpeaker();
                    $speakerStr = '';
                    if ($speakerIds && count($speakerIds) > 0) {
                        foreach ($speakerIds as $speakerId) {
                            $speaker = C4gReservationEventSpeakerModel::findByPk($speakerId);
                            if ($speaker) {
                                $speakerName = $speaker->title ? $speaker->title .'&nbsp;'.$speaker->firstname.'&nbsp;'.$speaker->lastname : $speaker->firstname.'&nbsp;'.$speaker->lastname;
                                $speakerStr = $speakerStr ? $speakerStr.',&nbsp;'.$speakerName : $speakerName;
                            }
                            $speakerField = new C4GTextField();
                            $speakerField->setFieldName('speaker');
                            $speakerField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['speaker']);
                            $speakerField->setFormField(true);
                            $speakerField->setEditable(false);
                            $speakerField->setDatabaseField(false);
                            $speakerField->setCondition($objConditionArr);
                            $speakerField->setInitialValue($speakerStr);
                            $speakerField->setMandatory(false);
                            $speakerField->setShowIfEmpty(false);
                            $speakerField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                            $speakerField->setRemoveWithEmptyCondition(true);
                            $speakerField->setNotificationField(true);
                            $speakerField->setSimpleTextWithoutEditing(true);
                            $speakerField->setStyleClass('eventdata eventdata_'.$type['id'].'-22'.$reservationObject->getId().' event-speaker');
                            $fieldList[] = $speakerField;
                        }
                    }

                    $topicIds = $reservationObject->getTopic();
                    $topicStr = '';
                    if ($topicIds && count($topicIds) > 0) {
                        foreach ($topicIds as $topicId) {
                            $topic = C4gReservationEventTopicModel::findByPk($topicId);
                            if ($topic) {
                                $topicName = $topic->topic;
                                $topicStr = $topicStr ? $topicStr.',&nbsp;'.$topicName : $topicName;
                            }
                            $topicField = new C4GTextField();
                            $topicField->setFieldName('topic');
                            $topicField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['topic']);
                            $topicField->setFormField(true);
                            $topicField->setEditable(false);
                            $topicField->setDatabaseField(false);
                            $topicField->setCondition($objConditionArr);
                            $topicField->setInitialValue($topicStr);
                            $topicField->setMandatory(false);
                            $topicField->setShowIfEmpty(false);
                            $topicField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                            $topicField->setRemoveWithEmptyCondition(true);
                            $topicField->setNotificationField(true);
                            $topicField->setSimpleTextWithoutEditing(true);
                            $topicField->setStyleClass('eventdata eventdata_'.$type['id'].'-22'.$reservationObject->getId().' event-topic');
                            $fieldList[] = $topicField;
                        }
                    }

                    $audienceIds = $reservationObject->getAudience();
                    $audienceStr = '';
                    if ($audienceIds && count($audienceIds) > 0) {
                        foreach ($audienceIds as $audienceId) {
                            $audience = C4gReservationEventAudienceModel::findByPk($audienceId);
                            if ($audience) {
                                $audienceName = $audience->targetAudience;
                                $audienceStr = $audienceStr ? $audienceStr.',&nbsp;'.$audienceName : $audienceName;
                            }
                            $audienceField = new C4GTextField();
                            $audienceField->setFieldName('audience');
                            $audienceField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['targetAudience']);
                            $audienceField->setFormField(true);
                            $audienceField->setEditable(false);
                            $audienceField->setDatabaseField(false);
                            $audienceField->setCondition($objConditionArr);
                            $audienceField->setInitialValue($audienceStr);
                            $audienceField->setMandatory(false);
                            $audienceField->setShowIfEmpty(false);
                            $audienceField->setAdditionalID($type['id'].'-22'.$reservationObject->getId());
                            $audienceField->setRemoveWithEmptyCondition(true);
                            $audienceField->setNotificationField(true);
                            $audienceField->setSimpleTextWithoutEditing(true);
                            $audienceField->setStyleClass('eventdata eventdata_'.$type['id'].'-22'.$reservationObject->getId().' event-audience');
                            $fieldList[] = $audienceField;
                        }
                    }
                }
            }

            $includedParams = $type['includedParams'];
            $includedParamsArr = [];

            if ($includedParams) {
                foreach ($includedParams as $paramId) {
                    $includedParam = C4gReservationParamsModel::findByPk($paramId);
                    if ($includedParam && $includedParam->caption && ($includedParam->price && $this->showPrices)) {
                        $includedParamsArr[] = ['id' => $paramId, 'name' => $includedParam->caption."<span class='price'>&nbsp;(+".number_format($includedParam->price,2)." €)</span>"];
                    } else if ($includedParam && $includedParam->caption) {
                        $includedParamsArr[] = ['id' => $paramId, 'name' => $includedParam->caption];
                    }
                }
            }

            if (count($includedParamsArr) > 0) {
                $includedParams = new C4GMultiCheckboxField();
                $includedParams->setFieldName('included_params');
                $includedParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['included_params']);
                $includedParams->setFormField(true);
                $includedParams->setEditable(false);
                $includedParams->setOptions($includedParamsArr);
                $includedParams->setMandatory(false);
                $includedParams->setModernStyle(false);
                $includedParams->setNotificationField(true);
                $includedParams->setCondition(array($condition));
                $includedParams->setRemoveWithEmptyCondition(true);
                $includedParams->setAdditionalID($type['id'].'-00'.$reservationObject->getId());
                $includedParams->setStyleClass('included-params');
                $includedParams->setAllChecked(true);
                $fieldList[] = $includedParams;
            }

            $params = $type['additionalParams'];
            $additionalParamsArr = [];

            if ($params) {
                foreach ($params as $paramId) {
                    $additionalParam = C4gReservationParamsModel::findByPk($paramId);
                    if ($additionalParam && $additionalParam->caption && ($additionalParam->price && $this->showPrices)) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption."<span class='price'>&nbsp;(+".number_format($additionalParam->price,2)." €)</span>"];
                    } else if ($additionalParam && $additionalParam->caption) {
                        $additionalParamsArr[] = ['id' => $paramId, 'name' => $additionalParam->caption];
                    }
                }
            }

            if (count($additionalParamsArr) > 0) {
                $additionalParams = new C4GMultiCheckboxField();
                $additionalParams->setFieldName('additional_params');
                $additionalParams->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['additional_params']);
                $additionalParams->setFormField(true);
                $additionalParams->setEditable(true);
                $additionalParams->setOptions($additionalParamsArr);
                $additionalParams->setMandatory(false);
                $additionalParams->setModernStyle(false);
                $additionalParams->setNotificationField(true);
                $additionalParams->setCondition(array($condition));
                $additionalParams->setRemoveWithEmptyCondition(true);
                $additionalParams->setAdditionalID($type['id'].'-00'.$reservationObject->getId());
                $additionalParams->setStyleClass('additional-params');
                $fieldList[] = $additionalParams;
            }
        }
        //end foreach type

        if (!$typelist || count($typelist) <= 0){
            $reservationNoneTypeField = new C4GLabelField();
            $reservationNoneTypeField->setDatabaseField(false);
            $reservationNoneTypeField->setInitialValue($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_none']);
            $fieldList[] = $reservationNoneTypeField;
        }

        $bookerHeadline = new C4GHeadlineField();
        $bookerHeadline->setTitle('Ihre Daten'); //ToDo
        $fieldList[] = $bookerHeadline;

        $salutation = [
            ['id' => 'man' ,'name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['man']],
            ['id' => 'woman','name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['woman']],
            ['id' => 'various','name' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['various']],
        ];

        $salutationField = new C4GSelectField();
        $salutationField->setFieldName('salutation');
        $salutationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation']);
        $salutationField->setSortColumn(false);
        $salutationField->setTableColumn(false);
        $salutationField->setOptions($salutation);
        $salutationField->setMandatory(false);
        $salutationField->setNotificationField(true);
        $salutationField->setStyleClass('salutation');
        $fieldList[] = $salutationField;

        $firstnameField = new C4GTextField();
        $firstnameField->setFieldName('firstname');
        $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
        $firstnameField->setColumnWidth(10);
        $firstnameField->setSortColumn(false);
        $firstnameField->setTableColumn(true);
        $firstnameField->setMandatory(true);
        $firstnameField->setNotificationField(true);
        $firstnameField->setStyleClass('firsname');
        $fieldList[] = $firstnameField;

        $lastnameField = new C4GTextField();
        $lastnameField->setFieldName('lastname');
        $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
        $lastnameField->setColumnWidth(10);
        $lastnameField->setSortColumn(false);
        $lastnameField->setTableColumn(true);
        $lastnameField->setMandatory(true);
        $lastnameField->setNotificationField(true);
        $lastnameField->setStyleClass('lastname');
        $fieldList[] = $lastnameField;

        $emailField = new C4GEmailField();
        $emailField->setFieldName('email');
        $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
        $emailField->setColumnWidth(10);
        $emailField->setSortColumn(false);
        $emailField->setTableColumn(false);
        $emailField->setMandatory(true);
        $emailField->setNotificationField(true);
        $emailField->setStyleClass('email');
        $fieldList[] = $emailField;

        $additionaldatas = StringUtil::deserialize($this->hide_selection);
        foreach ($additionaldatas as $rowdata) {
            $rowField = $rowdata['additionaldatas'];
            $initialValue = $rowdata['initialValue'];
            $rowMandatory = $rowdata['binding'];

            if ($rowField == "organisation") {
                $organisationField = new C4GTextField();
                $organisationField->setFieldName('organisation');
                $organisationField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation']);
                $organisationField->setColumnWidth(10);
                $organisationField->setSortColumn(false);
                $organisationField->setTableColumn(true);
                $organisationField->setMandatory($rowMandatory);
                $organisationField->setNotificationField(true);
                $organisationField->setStyleClass('organisation');
                $organisationField->setInitialValue($initialValue);
                $fieldList[] = $organisationField;
            } else if ($rowField == "title") {
                $titleField = new C4GTextField();
                $titleField->setFieldName('title');
                $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
                $titleField->setSortColumn(false);
                $titleField->setTableColumn(false);
                $titleField->setMandatory(false);
                $titleField->setNotificationField(true);
                $titleField->setStyleClass('title');
                $fieldList[] = $titleField;
            } else if ($rowField == "phone") {
                $phoneField = new C4GTelField();
                $phoneField->setFieldName('phone');
                $phoneField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
                $phoneField->setColumnWidth(10);
                $phoneField->setSortColumn(false);
                $phoneField->setMandatory($rowMandatory);
                $phoneField->setTableColumn(false);
                $phoneField->setNotificationField(true);
                $phoneField->setStyleClass('phone');
                $phoneField->setInitialValue($initialValue);
                $fieldList[] = $phoneField;
            } else if ($rowField == "address") {
                $addressField = new C4GTextField();
                $addressField->setFieldName('address');
                $addressField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
                $addressField->setColumnWidth(60);
                $addressField->setSortColumn(false);
                $addressField->setTableColumn(false);
                $addressField->setMandatory($rowMandatory);
                $addressField->setNotificationField(true);
                $addressField->setStyleClass('address');
                $addressField->setInitialValue($initialValue);
                $fieldList[] = $addressField;
            } else if ($rowField == "postal") {
                $postalField = new C4GPostalField();
                $postalField->setFieldName('postal');
                $postalField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
                $postalField->setColumnWidth(60);
                $postalField->setSize(5); //international 32
                $postalField->setSortColumn(false);
                $postalField->setTableColumn(false);
                $postalField->setMandatory($rowMandatory);
                $postalField->setNotificationField(true);
                $postalField->setStyleClass('postal');
                $postalField->setInitialValue($initialValue);
                $fieldList[] = $postalField;
            } else if ($rowField == "city") {
                $cityField = new C4GTextField();
                $cityField->setFieldName('city');
                $cityField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
                $cityField->setColumnWidth(60);
                $cityField->setSortColumn(false);
                $cityField->setTableColumn(false);
                $cityField->setMandatory($rowMandatory);
                $cityField->setNotificationField(true);
                $cityField->setStyleClass('city');
                $cityField->setInitialValue($initialValue);
                $fieldList[] = $cityField;
            } else if ($rowField == "salutation2") {
                $salutationField2 = new C4GSelectField();
                $salutationField2->setFieldName('salutation2');
                $salutationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['salutation']);
                $salutationField2->setSortColumn(false);
                $salutationField2->setTableColumn(false);
                $salutationField2->setOptions($salutation);
                $salutationField2->setMandatory($rowMandatory);
                $salutationField2->setNotificationField(true);
                $salutationField2->setStyleClass('salutation');
                $fieldList[] = $salutationField2;
            } else if ($rowField == "title2") {
                $titleField2 = new C4GTextField();
                $titleField2->setFieldName('title2');
                $titleField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
                $titleField2->setSortColumn(false);
                $titleField2->setTableColumn(false);
                $titleField2->setMandatory(false);
                $titleField2->setNotificationField(true);
                $titleField2->setStyleClass('title');
                $fieldList[] = $titleField2;
            } else if ($rowField == "firstname2") {
                $firstnameField2 = new C4GTextField();
                $firstnameField2->setFieldName('firstname2');
                $firstnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField2->setColumnWidth(10);
                $firstnameField2->setSortColumn(false);
                $firstnameField2->setTableColumn(true);
                $firstnameField2->setMandatory($rowMandatory);
                $firstnameField2->setNotificationField(true);
                $firstnameField2->setStyleClass('firsname');
                $fieldList[] = $firstnameField2;
            } else if ($rowField == "lastname2") {
                $lastnameField2 = new C4GTextField();
                $lastnameField2->setFieldName('lastname2');
                $lastnameField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField2->setColumnWidth(10);
                $lastnameField2->setSortColumn(false);
                $lastnameField2->setTableColumn(true);
                $lastnameField2->setMandatory($rowMandatory);
                $lastnameField2->setNotificationField(true);
                $lastnameField2->setStyleClass('lastname');
                $fieldList[] = $lastnameField2;
            } else if ($rowField == "email2") {
                $emailField2 = new C4GEmailField();
                $emailField2->setFieldName('email2');
                $emailField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                $emailField2->setColumnWidth(10);
                $emailField2->setSortColumn(false);
                $emailField2->setTableColumn(false);
                $emailField2->setMandatory($rowMandatory);
                $emailField2->setNotificationField(true);
                $emailField2->setStyleClass('email');
                $fieldList[] = $emailField2;
            }if ($rowField == "organisation2") {
                $organisationField2 = new C4GTextField();
                $organisationField2->setFieldName('organisation2');
                $organisationField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['organisation']);
                $organisationField2->setColumnWidth(10);
                $organisationField2->setSortColumn(false);
                $organisationField2->setTableColumn(true);
                $organisationField2->setMandatory($rowMandatory);
                $organisationField2->setNotificationField(true);
                $organisationField2->setStyleClass('organisation');
                $organisationField2->setInitialValue($initialValue);
                $fieldList[] = $organisationField2;
            } else if ($rowField == "phone2") {
                $phoneField2 = new C4GTelField();
                $phoneField2->setFieldName('phone2');
                $phoneField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['phone']);
                $phoneField2->setColumnWidth(10);
                $phoneField2->setSortColumn(false);
                $phoneField2->setMandatory($rowMandatory);
                $phoneField2->setTableColumn(false);
                $phoneField2->setNotificationField(true);
                $phoneField2->setStyleClass('phone');
                $phoneField2->setInitialValue($initialValue);
                $fieldList[] = $phoneField2;
            } else if ($rowField == "address2") {
                $addressField2 = new C4GTextField();
                $addressField2->setFieldName('address2');
                $addressField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['address']);
                $addressField2->setColumnWidth(60);
                $addressField2->setSortColumn(false);
                $addressField2->setTableColumn(false);
                $addressField2->setMandatory($rowMandatory);
                $addressField2->setNotificationField(true);
                $addressField2->setStyleClass('address');
                $addressField2->setInitialValue($initialValue);
                $fieldList[] = $addressField2;
            } else if ($rowField == "postal2") {
                $postalField2 = new C4GPostalField();
                $postalField2->setFieldName('postal2');
                $postalField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['postal']);
                $postalField2->setColumnWidth(60);
                $postalField2->setSize(5); //international 32
                $postalField2->setSortColumn(false);
                $postalField2->setTableColumn(false);
                $postalField2->setMandatory($rowMandatory);
                $postalField2->setNotificationField(true);
                $postalField2->setStyleClass('postal');
                $postalField2->setInitialValue($initialValue);
                $fieldList[] = $postalField2;
            } else if ($rowField == "city2") {
                $cityField2 = new C4GTextField();
                $cityField2->setFieldName('city2');
                $cityField2->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['city']);
                $cityField2->setColumnWidth(60);
                $cityField2->setSortColumn(false);
                $cityField2->setTableColumn(false);
                $cityField2->setMandatory($rowMandatory);
                $cityField2->setNotificationField(true);
                $cityField2->setStyleClass('city');
                $cityField2->setInitialValue($initialValue);
                $fieldList[] = $cityField2;
            } else if ($rowField == "comment") {
                $commentField = new C4GTextareaField();
                $commentField->setFieldName('comment');
                $commentField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['comment']);
                $commentField->setColumnWidth(60);
                $commentField->setSortColumn(false);
                $commentField->setTableColumn(false);
                $commentField->setMandatory($rowMandatory);
                $commentField->setNotificationField(true);
                $commentField->setStyleClass('comment');
                $commentField->setInitialValue($initialValue);
                $fieldList[] = $commentField;
            } else if ($rowField == "additionalHeadline") {
                $headlineField = new C4GHeadlineField();
                $headlineField->setTitle($initialValue);
                $fieldList[] = $headlineField;
            } else if ($rowField == "participants") {
                $participantsKey = new C4GKeyField();
                $participantsKey->setFieldName('id');
                $participantsKey->setComparable(false);
                $participantsKey->setEditable(false);
                $participantsKey->setHidden(true);
                $participantsKey->setFormField(true);

                $participantsForeign = new C4GForeignKeyField();
                $participantsForeign->setFieldName('pid');
                $participantsForeign->setHidden(true);
                $participantsForeign->setFormField(true);

                $participants = [];


                $titleField = new C4GTextField();
                $titleField->setFieldName('title');
                $titleField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['title']);
                $titleField->setSortColumn(false);
                $titleField->setTableColumn(false);
                $titleField->setMandatory(false);
                $titleField->setNotificationField(true);
                $participants[] = $titleField;

                $firstnameField = new C4GTextField();
                $firstnameField->setFieldName('firstname');
                $firstnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['firstname']);
                $firstnameField->setColumnWidth(10);
                $firstnameField->setSortColumn(false);
                $firstnameField->setTableColumn(true);
                $firstnameField->setMandatory(true);
                $firstnameField->setNotificationField(true);
                $participants[] = $firstnameField;

                $lastnameField = new C4GTextField();
                $lastnameField->setFieldName('lastname');
                $lastnameField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['lastname']);
                $lastnameField->setColumnWidth(10);
                $lastnameField->setSortColumn(false);
                $lastnameField->setTableColumn(true);
                $lastnameField->setMandatory(true);
                $lastnameField->setNotificationField(true);
                $participants[] = $lastnameField;

                $emailField = new C4GEmailField();
                $emailField->setFieldName('email');
                $emailField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['email']);
                $emailField->setColumnWidth(10);
                $emailField->setSortColumn(false);
                $emailField->setTableColumn(false);
                $emailField->setMandatory(false);
                $emailField->setNotificationField(true);
                $participants[] = $emailField;

                $reservationParticipants = new C4GSubDialogField();
                $reservationParticipants->setFieldName('participants');
                $reservationParticipants->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['participants']);
                $reservationParticipants->setAddButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['addParticipant']);
                $reservationParticipants->setRemoveButton($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipant']);
                $reservationParticipants->setTable('tl_c4g_reservation_participants');
                $reservationParticipants->addFields($participants);
                $reservationParticipants->setKeyField($participantsKey);
                $reservationParticipants->setForeignKeyField($participantsForeign);
                $reservationParticipants->setMandatory($rowMandatory);
                $reservationParticipants->setRemoveButtonMessage($GLOBALS['TL_LANG']['fe_c4g_reservation']['removeParticipantMessage']);
                $reservationParticipants->setMax(intval($type->maxParticipantsPerBooking) > 0 ? $type->maxParticipantsPerBooking : -1); //ToDo Test
                $fieldList[] = $reservationParticipants;
            }
        }

        $reservationIdField = new C4GTextField();
        $reservationIdField->setFieldName('reservation_id');
        $reservationIdField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id']);
        $reservationIdField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_reservation_id']);
        $reservationIdField->setColumnWidth(10);
        $reservationIdField->setSortColumn(true);
        $reservationIdField->setTableColumn(true);
        $reservationIdField->setMandatory(false);
        $reservationIdField->setInitialValue(C4GBrickCommon::getUUID());
        $reservationIdField->setTableRow(false);
        $reservationIdField->setEditable(false);
        $reservationIdField->setUnique(true);
        $reservationIdField->setNotificationField(true);
        $reservationIdField->setDbUnique(true);
        $reservationIdField->setSimpleTextWithoutEditing(true);
        $reservationIdField->setDatabaseField(true);
        $reservationIdField->setDbUniqueResult($GLOBALS['TL_LANG']['fe_c4g_reservation']['reservation_id_exists']);
        $reservationIdField->setDbUniqueAdditionalCondition("tl_c4g_reservation.cancellation <> '1' AND tl_c4g_reservation.beginDate > UNIX_TIMESTAMP(NOW())");
        $reservationIdField->setStyleClass('reservation-id');
        $fieldList[] = $reservationIdField;

        if ($this->privacy_policy_text) {
            $privacyPolicyText = new C4GTextField();
            $privacyPolicyText->setSimpleTextWithoutEditing(true);
            $privacyPolicyText->setFieldName('privacy_policy_text');
            $privacyPolicyText->setInitialValue(\Contao\Controller::replaceInsertTags($this->privacy_policy_text));
            $privacyPolicyText->setSize(4);
            $privacyPolicyText->setTableColumn(false);
            $privacyPolicyText->setEditable(false);
            $privacyPolicyText->setDatabaseField(false);
            $privacyPolicyText->setMandatory(false);
            $privacyPolicyText->setNotificationField(false);
            $privacyPolicyText->setStyleClass('privacy-policy-text');
            $fieldList[] = $privacyPolicyText;
        }

        $agreedField = new C4GCheckboxField();
        $agreedField->setFieldName('agreed');
        $agreedField->setTitle($GLOBALS['TL_LANG']['fe_c4g_reservation']['agreed']);
        if ($this->privacy_policy_site) {
            $href = \Contao\Controller::replaceInsertTags('{{link_url::' . $this->privacy_policy_site . '}}');
            $agreedField->setDescription($GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed'] . '<a href="' . $href . '" target="_blank" rel="noopener">' . $GLOBALS['TL_LANG']['fe_c4g_reservation']['desc_agreed_link_text'] . '</a>');
        }
        $agreedField->setTableRow(false);
        $agreedField->setColumnWidth(5);
        $agreedField->setSortColumn(false);
        $agreedField->setTableColumn(false);
        $agreedField->setMandatory(true);
        $agreedField->setNotificationField(true);
        $agreedField->setStyleClass('agreed');
        $fieldList[] = $agreedField;

        $clickButton = new C4GBrickButton(
            C4GBrickConst::BUTTON_CLICK,
            $this->reservationButtonCaption ? \Contao\Controller::replaceInsertTags($this->reservationButtonCaption) : $GLOBALS['TL_LANG']['fe_c4g_reservation']['button_reservation'],
            $visible = true,
            $enabled = true,
            $action = '',
            $accesskey = '',
            $defaultByEnter = true);

        $buttonField = new C4GButtonField($clickButton);
        $buttonField->setOnClickType(C4GBrickConst::ONCLICK_TYPE_SERVER);
        $buttonField->setOnClick('clickReservation');
        $buttonField->setWithoutLabel(true);
        $fieldList[] = $buttonField;

        //ToDo load location table
        $contact_name = new C4GTextField();
        $contact_name->setFieldName('contact_name');
        $contact_name->setSortColumn(false);
        $contact_name->setFormField(false);
        $contact_name->setTableColumn(true);
        $contact_name->setNotificationField(true);
        $contact_name->setStyleClass('contact-name');
        $fieldList[] = $contact_name;

        $contact_phone = new C4GTelField();
        $contact_phone->setFieldName('contact_phone');
        $contact_phone->setFormField(false);
        $contact_phone->setTableColumn(false);
        $contact_phone->setNotificationField(true);
        $contact_phone->setStyleClass('contact-phone');
        $fieldList[] = $contact_phone;

        $contact_email = new C4GEmailField();
        $contact_email->setFieldName('contact_email');
        $contact_email->setTableColumn(false);
        $contact_email->setFormField(false);
        $contact_email->setNotificationField(true);
        $contact_email->setStyleClass('contact-email');
        $fieldList[] = $contact_email;


        $contact_street = new C4GTextField();
        $contact_street->setFieldName('contact_street');
        $contact_street->setTableColumn(false);
        $contact_street->setFormField(false);
        $contact_street->setNotificationField(true);
        $contact_street->setStyleClass('contact-street');
        $fieldList[] = $contact_street;


        $contact_postal = new C4GPostalField();
        $contact_postal->setFieldName('contact_postal');
        $contact_postal->setFormField(false);
        $contact_postal->setTableColumn(false);
        $contact_postal->setNotificationField(true);
        $contact_postal->setStyleClass('contact-postal');
        $fieldList[] = $contact_postal;


        $contact_city = new C4GTextField();
        $contact_city->setFieldName('contact_city');
        $contact_city->setTableColumn(false);
        $contact_city->setFormField(false);
        $contact_city->setNotificationField(true);
        $contact_city->setStyleClass('contact-city');
        $fieldList[] = $contact_city;

        $this->fieldList = $fieldList;
    }




    public function createIcs($beginDate, $beginTime, $endDate, $endTime, $object, $type)
    {
        $locationId = ($object && $object->location) ? $object->location : $type->location;
        if ($locationId) {
            $location = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_location WHERE id=?")
                ->execute($locationId);
            if ($location && $location->ics && $location->icsPath) {
                $contact_street = $location->contact_street;
                $contact_postal = $location->contact_postal;
                $contact_city = $location->contact_city;
                $contact_name = $location->contact_name;
                $contact_email = $location->contact_email;

                $dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
                $timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
                $timezone   = $GLOBALS['TL_CONFIG']['timeZone'];

                $icstimezone = 'TZID='.$timezone;
                $icsdaylightsaving= date('I');
                $icsprodid = $contact_name;
                $icslocation = $contact_street ." ". $contact_postal." ". $contact_city;
                $icsuid = $contact_email;

                $local_tz = new DateTimeZone($timezone);
                $localTime = new DateTime($beginTime, $local_tz); //ToDo Test
//                $difference = $beginTime->diff($local);
//
//                if ($icsdaylightsaving == 1) {
//                    $beginTime = $beginTime - $difference - 3600;
//                }
//                if ($icsdaylightsaving == 0) {
//                    $beginTime = $beginTime - $difference;
//                }

                $b_date = date('Ymd', strtotime($beginDate));
                $b_time = date('His', $localTime);
                $icsdate = $b_date . 'T' . $b_time . 'Z';

                $icsalert = $location->icsAlert;

                $residence = $object->min_residence_time;
                $time_int = $object->time_interval;

                $icssummary = $object->caption;

                $icsalert = $icsalert * 60;
                $icsalert = '-PT'.$icsalert.'M';

                if ($residence != 0) {
                    $residence = $residence * 3600;
                    $e_date = date('Ymd',strtotime($beginDate));
                    $e_time = $beginTime + $residence;
                    $e_time = date('His',$e_time);
                    $icsenddate =$e_date . 'T' . $e_time. 'Z';
                } else if ($time_int) {
                    $time_int = $time_int * 3600;
                    $e_date = date('Ymd',strtotime($beginDate));
                    $e_time = $beginTime + $time_int;
                    $e_time = date('His',$e_time);
                    $icsenddate =$e_date . 'T' . $e_time. 'Z';
                } else if ($type->reservationObjectType == '2') {  //event
                    $e_date = date('Ymd', $beginDate);
                    $e_time = date('His', $beginTime);
                    $icsenddate =$e_date . 'T' . $e_time. 'Z';
                }

               $fileId = sprintf("%05d", $type->id).sprintf("%05d",$object->id);
               $filename = $location->icsPath.'/'.$fileId.'/'.'reservation.isc';
                try {
                    $ics = new File($filename);
                } catch (\Exception $exception) {
                    $fs = new Filesystem();
                    $fs->touch($filename);
                    $ics = new File($filename);
                }
                $ics->openFile("w")->fwrite("BEGIN:VCALENDAR\nVERSION:2.0\nPRODID:$icsprodid\nMETHOD:PUBLISH\nBEGIN:VEVENT\nUID:$icsuid\nLOCATION:$icslocation\nSUMMARY:$icssummary\nCLASS:PUBLIC\nDESCRIPTION:$icssummary\nDTSTART:$icsdate\nDTEND:$icsenddate\nBEGIN:VALARM\nTRIGGER:$icsalert\nACTION:DISPLAY\nDESCRIPTION:$icssummary\nEND:VALARM\nEND:VEVENT\nEND:VCALENDAR\n");
            }
        }
    }

    public function clickReservation($values, $putVars)
    {
        $type = $putVars['reservation_type'];

        //ToDO Test
        if ($type && $type['notification_type']) {
            $this->dialogParams->setNotificationType($type['notification_type']);
            $this->notification_type = $type['notification_type'];
        }
        $newFieldList = [];
        $removedFromList = [];
        foreach ($this->getFieldList() as $key=>$field) {
            $additionalId = $field->getAdditionalID();
            if ($additionalId && (($additionalId != $type) && (strpos($additionalId, strval($type.'-')) !== 0))) {
                unset($putVars[$field->getFieldName()."_".$additionalId]);
                continue;
            } else if ($additionalId) {
                $removedFromList[$field->getFieldName()] = $field->getAdditionalID();
                unset($putVars[$field->getFieldName()]);
            }

            $reservationType = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_type WHERE id=? AND published='1'")
                ->execute($type);
            $isEvent = $reservationType->reservationObjectType && $reservationType->reservationObjectType === '2' ? true : false;
            if ($isEvent) {
                $key = "reservation_object_event_" . $type;
                $resObject = $putVars[$key];
                $reservationObject = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE id=? AND published='1'")
                    ->execute($resObject);
            } else {
                $key = "reservation_object_" . $type;
                $resObject = $putVars[$key];
                $reservationObject = $this->Database->prepare("SELECT * FROM tl_c4g_reservation_object WHERE id=? AND published='1'")
                    ->execute($resObject);
            }

            $contact_name = $reservationType->contact_name;
            $contact_email = $reservationType->contact_email;
            $vcard = $reservationObject->vcard_show;
            if ($vcard) {
                $contact_street = $reservationObject->contact_street;
                $contact_phone = $reservationObject->contact_phone;
                $contact_postal = $reservationObject->contact_postal;
                $contact_city = $reservationObject->contact_city;
            } else {
                $contact_street = $reservationType->contact_street;
                $contact_phone = $reservationType->contact_phone;
                $contact_postal = $reservationType->contact_postal;
                $contact_city = $reservationType->contact_city;
            }

            $putVars['contact_name'] = $contact_name;
            $putVars['contact_phone'] = $contact_phone;
            $putVars['contact_email'] = $contact_email;
            $putVars['contact_street'] = $contact_street;
            $putVars['contact_postal'] = $contact_postal;
            $putVars['contact_city'] = $contact_city;

            if ($field->getFieldName() && (!$removedFromList[$field->getFieldName()] || ($removedFromList[$field->getFieldName()] == $field->getAdditionalId()))) {
                $newFieldList[] = $field;
            }
        }

        if ($isEvent) {
            $putVars['reservationObjectType'] = '2';
            $objectId = $putVars['reservation_object_event_' . $type];
            $t = 'tl_c4g_reservation';
            $arrColumns = array("$t.reservation_object=$objectId AND $t.reservationObjectType='2' AND NOT $t.cancellation='1'");
            $arrValues = array();
            $arrOptions = array();
            $reservations = C4gReservationModel::findBy($arrColumns, $arrValues, $arrOptions);

            $reservationCount = count($reservations);
            $reservationEventObject = C4gReservationEventModel::findOneBy('pid', $objectId);
            $desiredCapacity =  $reservationEventObject && $reservationEventObject->maxParticipants ? $reservationEventObject->maxParticipants : 0;

            if ($desiredCapacity && ((($reservationCount / $desiredCapacity) * 100) >= 100)) {
                return ['usermessage' => $GLOBALS['TL_LANG']['fe_c4g_reservation']['fully_booked']];
            }

            $putVars['reservation_object'] = $objectId;

            //implement all event possibilities
            $putVars['beginDate'] = $reservationObject->startDate ? intvaL($reservationObject->startDate) : 0;
            $putVars['beginTime'] = $reservationObject->startTime ? intval($reservationObject->startTime) : 0;
            $putVars['endDate'] = $reservationObject->endDate ? intval($reservationObject->endDate) : 0;
            $putVars['endTime'] = $reservationObject->endTime ? intval($reservationObject->endTime) : 0;

            $beginDate = $putVars['beginDate'];
            $beginTime = $putVars['beginTime'];
            $endDate   = $putVars['endDate'];
            $endTime   = $putVars['endTime'];
       } else {
            $putVars['reservationObjectType'] = '1';
            $beginDate = $putVars['beginDate_'.$type];

            $beginTime = 0;
            foreach ($putVars as $key => $value) {
                if (strpos($key, "beginTime_".$type) !== false) {
                    if ($value) {
                        $beginTime = $value;
                        break;
                    }
                }
            }

            $time_interval = $reservationObject->time_interval;
            $min_residence_time = $reservationObject->min_residence_time;
            $max_residence_time = $reservationObject->max_residence_time;

            switch ($reservationType->periodType) {
                case 'minute':
                    $interval = 60;
                    break;
                case 'hour':
                    $interval = 3600;
                    break;
                default: '';
            }

            $duration = $putVars['duration'];
            if ($duration && (($duration >= $min_residence_time) && ($duration <= $max_residence_time))) {
                //$duration = $duration;
            } else {
                $duration = $time_interval;
            }

            $duration = $duration * $interval;
            $endTime = $beginTime + $duration;

            $putVars['endDate'] = strtotime($putVars['beginDate_'.$type]); //ToDo multiple days
            $putVars['endTime'] = $endTime;
        }

        $action = new C4GSaveAndRedirectDialogAction($this->dialogParams, $this->getListParams(), $newFieldList, $putVars, $this->getBrickDatabase());
        $action->setModule($this);

        $vcardObject = $reservationEventObject ? $reservationEventObject : $reservationObject;
        $this->createIcs($beginDate, $beginTime, $endDate, $endtTime, $vcardObject, $reservationType);

        return $result = $action->run();
    }

    public function getCurrentTimeset($values, $putVars)
    {
        $date = $values[2];
        $additionalParam = $values[3];
        $duration = $values[4];
        $weekday = -1;
        $wd = -1;

        //hotfix dates with slashes
        $date = str_replace("~", "/", $date);
        if ($date)  {
            $format = $GLOBALS['TL_CONFIG']['dateFormat'];

            $tsdate = \DateTime::createFromFormat($format, $date);
            if ($tsdate) {
                $tsdate->Format($format);
                $tsdate->setTime(0,0,0);
                $tsdate = $tsdate->getTimestamp();
            } else {
                $format = "d/m/Y";
                $tsdate = \DateTime::createFromFormat($format, $date);
                if ($tsdate) {
                    $tsdate->Format($format);
                    $tsdate->setTime(0,0,0);
                    $tsdate = $tsdate->getTimestamp();
                } else {
                    $tsdate = strtotime($date);
                }
            }

            $datetime = $tsdate;//strtotime($date);
            $wd = date("w", $datetime);
            switch ($wd) {
                case 0:
                    $weekday = 'su';
                    break;
                case 1:
                    $weekday = 'mo';
                    break;
                case 2:
                    $weekday = 'tu';
                    break;
                case 3:
                    $weekday = 'we';
                    break;
                case 4:
                    $weekday = 'th';
                    break;
                case 5:
                    $weekday = 'fr';
                    break;
                case 6:
                    $weekday = 'sa';
                    break;
            }
        }

        $objects = C4gReservationObjectModel::getReservationObjectList(array($additionalParam));
        $withEndTimes = $this->showEndTime;
        $withFreeSeats = $this->showFreeSeats;

        $times = C4gReservationObjectModel::getReservationTimes($objects, $additionalParam, $weekday, $date, $duration, $withEndTimes, $withFreeSeats);

        if ($additionalParam) {
            if ($this->fieldList) {
                foreach ($this->fieldList as $key => $field) {
                    if (($field->getFieldName() == 'beginTime') && ($field->getAdditionalId() == $additionalParam . '00' . $wd)) {
                        $this->fieldList[$key]->setOptions($times);
                        break;
                    }
                }
            }
        }

        return array(
            'times' => $times
        );
    }

}

