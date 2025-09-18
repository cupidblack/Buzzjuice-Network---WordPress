import {CLASS_BIGOPTION_SELECTED, CLASS_OPTION_LABEL, SELECTOR_TYPE, OPENER_TYPE} from "../configs/_selectorConfig";


/**
 *
 * @param {DzsSelector} selfInstance
 */
export function opener_reinit_checkMultipleActiveOptions(selfInstance) {

  let isGoingToUpdateLabel = false;
  selfInstance.$openerMain.find('input[type="checkbox"]').each(function () {
    var $t = jQuery(this);

    if ($t.prop('checked')) {
      if ($t.parent().hasClass('multiple-option')) {

        const ind = $t.parent().parent().children().index($t.parent());
        isGoingToUpdateLabel = true;
        opener_selectOption(selfInstance, ind, false);
      }
    } else {

      if ($t.parent().hasClass('multiple-option') && $t.parent().hasClass(CLASS_BIGOPTION_SELECTED)) {

        const ind = $t.parent().parent().children().index($t.parent());
        isGoingToUpdateLabel = true;
        opener_selectOption(selfInstance, ind, true);
      }
    }
  })

  if (isGoingToUpdateLabel) {
    opener_updateMainLabel(selfInstance);
  }
}

/**
 *
 * @param {DzsSelector} selfInstance
 */
export function opener_updateMainLabel(selfInstance) {

  const stringSelected = opener_wrapperHeadConstructStringSelectedOptions(selfInstance);

  selfInstance.view_selectWrapperHeaderSetLabel(stringSelected);
}


/**
 * used for construction the label
 * @param {DzsSelector} selfInstance
 * @returns {string}
 */
export function opener_wrapperHeadConstructStringSelectedOptions(selfInstance) {

  let selectorHeadLabel = '';

  if (selfInstance.isOpener) {
    selfInstance.$openerMain.children().each(function () {
      const $openerMainChild = jQuery(this);

      if ($openerMainChild.hasClass(CLASS_BIGOPTION_SELECTED)) {


        if (selectorHeadLabel) {
          selectorHeadLabel += ', ';
        }


        const $optionLabelText = $openerMainChild.find(CLASS_OPTION_LABEL);
        if ($optionLabelText.length) {

          selectorHeadLabel += $optionLabelText.text();
        } else {
          selectorHeadLabel += $openerMainChild.html();
        }
      }
    })
  }

  if (!selectorHeadLabel) {
    selectorHeadLabel = selfInstance.initOptions.placeholder;
  }

  return selectorHeadLabel;
}

/**
 *
 * @param {DzsSelector} selfInstance
 * @returns {(function(*=): void)|*}
 */
export function opener_open(selfInstance) {

  return function () {

    const o = selfInstance.initOptions;
    const isOpenerActive = selfInstance.$wrapperMain.hasClass(CLASS_BIGOPTION_SELECTED);

    if (!isOpenerActive) {
      // -- close other openers
      document.querySelectorAll('.dzs-select-wrapper').forEach(($selectWrapper_) => {
        if ($selectWrapper_ === selfInstance.$wrapperMain.get(0)) {
          return false;
        }
        if ($selectWrapper_.getAttribute('class').indexOf('opener-') > -1) {
          if ($selectWrapper_.api_close_opener) {
            $selectWrapper_.api_close_opener();
          }
        }
      })
    }
    selfInstance.$wrapperMain.addClass(CLASS_BIGOPTION_SELECTED);
    setTimeout(function () {
      selfInstance.$wrapperMain.addClass('active-animation');
    }, 50);
    if (selfInstance.$openerExternalArea) {
      window.opener_externalOpenerToggle(selfInstance);
    }

    if (o.opener === OPENER_TYPE['LIST']) {

    }
  }
}


export function opener_clearActiveChecked(selfInstance) {

  selfInstance.$openerMain.children().removeClass(CLASS_BIGOPTION_SELECTED);
}


/**
 *
 * @param {DzsSelector} selfInstance
 * @param {number} currentIndexSelected
 * @param {boolean|undefined} forceIsActive
 */
export function opener_selectOption(selfInstance, currentIndexSelected, forceIsActive) {


  let currentOptionIsChecked = false;


  const $openerChildAtIndex = selfInstance.$openerMain.children().eq(currentIndexSelected);

  if ($openerChildAtIndex.hasClass(CLASS_BIGOPTION_SELECTED)) {
    currentOptionIsChecked = true;
  }

  if (forceIsActive !== undefined) {
    currentOptionIsChecked = forceIsActive;
  }



  // -- clear if only one
  if (selfInstance.selectorOptions.type === SELECTOR_TYPE.SELECT) {
    opener_clearActiveChecked(selfInstance);
  }


  if (!currentOptionIsChecked) {
    $openerChildAtIndex.addClass(CLASS_BIGOPTION_SELECTED);
  } else {
    // -- current option is already checked
    if (selfInstance.selectorOptions.type === SELECTOR_TYPE.MULTIPLE_CHECKBOXES) {
      $openerChildAtIndex.removeClass(CLASS_BIGOPTION_SELECTED);
    }

    if (selfInstance.selectorOptions.type === SELECTOR_TYPE.SELECT) {
      if (selfInstance.typeSelectIsAllowEmptyOptions === true) {
        selfInstance.$selectInputForm.val('');
      }
      if (selfInstance.typeSelectIsAllowEmptyOptions === false) {
        $openerChildAtIndex.addClass(CLASS_BIGOPTION_SELECTED);
      }
    }
  }
}

/**
 *
 * @param {DzsSelector} selfInstance
 * @returns {(function(*=): void)|*}
 */
export function opener_close(selfInstance) {

  return function () {


    var o = selfInstance.initOptions;


    var delay = 300;

    selfInstance.$wrapperMain.removeClass('active-animation');
    if (o.opener === OPENER_TYPE['LIST']) {
      delay = 0;
    }
    setTimeout(function () {
      selfInstance.$wrapperMain.removeClass(CLASS_BIGOPTION_SELECTED);
    }, delay);


    if (String(selfInstance.feedElementClass).indexOf(OPENER_TYPE['BIGOPTIONS']) > -1) {


      ;
    }


    if (selfInstance.$openerExternalArea) {
      opener_externalOpenerClose(selfInstance);
    }

  }
}
