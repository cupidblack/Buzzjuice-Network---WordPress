import React from 'react';

// import { __ } from '@wordpress/i18n';
// import { TextControl, SelectControl } from '@wordpress/components';
// import {
//   PlainText,
//   MediaUploader
// } from '@wordpress/block-editor';

let __ = (arg) => {
  return arg;
};

if (wp.i18n) {
  __ = wp.i18n.__;
}


const {TextControl, SelectControl, TextareaControl} = window.wp.components;
const {
  PlainText,
} = window.wp.blockEditor;
const {MediaUpload} = window.wp.editor;

// console.log('MediaUpload -0', MediaUpload);

const IGNORED_KEYS = ['source', 'item_source', 'layout'];


const generatePropertyComponent = (configOptionKey, optionConfig, argsInputForm, Sidenote, props) => {


  const divAtts = {
    className: "dzs-gutenberg--inspector-setting check-instanceid ",
    'instanceid': configOptionKey,
  };
  if (optionConfig.dependency) {
    divAtts['data-dependency'] = JSON.stringify(optionConfig.dependency);
  }


  if (['dzs_row', 'custom_html', 'dzs_col_md_6_end', 'dzs_col_md_6'].indexOf(optionConfig.type)>-1) {
    return (<></>);
  }

  if (optionConfig.type === 'select') {

    if (optionConfig.choices && !(optionConfig.options)) {
      optionConfig.options = optionConfig.choices;
    }


    divAtts.className += 'type-' + optionConfig.type;
    return (
      <div {...divAtts}>
        <SelectControl
          {...argsInputForm}
          options={optionConfig.options}
        />
        {Sidenote}
      </div>

    );
  }


  if (optionConfig.type === 'attach') {

    divAtts.className += 'type-' + optionConfig.type;
    if (optionConfig.upload_type) {

      argsInputForm.allowedTypes = [optionConfig.upload_type];
    }
    argsInputForm.onChange = null;


    // console.log('attach argsInputForm -> ', argsInputForm);
    return (
      <div {...divAtts}>
        <label className="components-base-control__label">{optionConfig.title}</label>
        <MediaUpload
          {...argsInputForm}
          onSelect={(imageObject) => {
            props.setAttributes({[configOptionKey]: imageObject.url});
          }}
          render={({open}) => (
            <div className="render-song-selector">
              {props.attributes[configOptionKey] ? (
                <PlainText
                  format="string"
                  formattingControls={[]}
                  placeholder={__('Input song name')}
                  onChange={(val) => props.setAttributes({[configOptionKey]: val})}
                  value={props.attributes[configOptionKey]}
                />
              ) : ""}
              <button className="button-secondary" onClick={open}>{props.uploadButtonLabel}</button>
            </div>
          )}
        />
        {Sidenote}
      </div>
    )
      ;
  }


  let theControl = <TextControl
    {...argsInputForm}
  />;

  if (optionConfig.type === 'textarea') {

    theControl = <TextareaControl
      {...argsInputForm}
    />;
  }

  divAtts.className += 'type-' + optionConfig.type;
  return (
    <div {...divAtts}>
      {theControl}
      {Sidenote}
    </div>
  )
    ;

}

export default class CustomInspectorControls extends React.Component {
  constructor(props) {
    super(props);
    this.props = props;
  }

  render() {


    if (this.props.configAttributes) {
      const sidenoteReplaces = this.props.sidenoteReplaces;
      console.log('this.props.sidenoteReplaces - ', this.props.sidenoteReplaces);
      return Object.keys(this.props.configAttributes).map((optionIndex) => {

        const props = this.props;
        let optionConfig = this.props.configAttributes[optionIndex];


        if (!optionConfig) {
          return '';
        }
        const configOptionKey = optionConfig.configOptionKey ? optionConfig.configOptionKey : optionIndex;

        if (IGNORED_KEYS.indexOf(configOptionKey) > -1) {
          return '';
        }

        if (configOptionKey === 'cat') {
          optionConfig.options = dzswtl_settings.cats;
        }

        const argsInputForm = {
            label: optionConfig.title,
            value: props.attributes[configOptionKey] ? props.attributes[configOptionKey] : '',
            instanceId: configOptionKey,
            className: ' dzs-dependency-field',
            onChange: (value) => {
              props.setAttributes({[configOptionKey]: value});
            }
          }
        ;


        let Sidenote = null;
        if (optionConfig.description && !optionConfig.sidenote) {
          optionConfig.sidenote = optionConfig.description;
        }
        let theDescription = String(optionConfig.sidenote);

        var auxr = /{\[(.*?)\]}/g;
        let aux;
        while (aux = auxr.exec(theDescription)){
          if(aux[1]){
            const theVal = aux[1];
            if(sidenoteReplaces && sidenoteReplaces[theVal]!==undefined){

              console.log('sidenoteReplaces[theVal] -' ,sidenoteReplaces[theVal]);
              const replaceVal = sidenoteReplaces[theVal](props);

              theDescription = theDescription.replace(`{[${theVal}]}`, `${replaceVal}`);
            }
          }
        }

        if (theDescription) {
          Sidenote = (
            <div className="sidenote" dangerouslySetInnerHTML={{__html: theDescription}}/>
          )
        }


        // console.log('props - ', props);
        return generatePropertyComponent(configOptionKey, optionConfig, argsInputForm, Sidenote, props);

      });
    }

    return null;

  }
}