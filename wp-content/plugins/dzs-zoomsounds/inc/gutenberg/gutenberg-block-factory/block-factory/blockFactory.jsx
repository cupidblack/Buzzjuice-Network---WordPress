import {sanitizeBlockAttributes} from './js_block-factory/blockFactoryFunctions';

import CustomInspectorControls from "./js_common/CustomInspectorControls";
import {BlockFactoryContent} from './blockFactoryContent';
import './blockFactory.scss';


let __ = (arg) => {
  return arg;
};
if (wp.i18n) {
  __ = wp.i18n.__;
}
const {Panel, PanelBody} = window.wp.components;
const {registerBlockType} = window.wp.blocks;
const {InspectorControls} = window.wp.blockEditor;


console.log('registerBlockType-  ', registerBlockType);

export class BlockFactory {
  constructor(props) {

    this.blockAttributes = {
      blockKey: '',
      blockTitle: '',
      keywords: [
        __('Shop'),
        __('Table'),],
      blockIcon: 'format-audio',
      blockCategory: 'common',
      blockDescription: __('Customizable woocommerce shop layout'),
      configAttributes: {},
      sidenoteReplaces: {},
      adminPreviewComponent: null,
    }

    this.blockAttributes = {...this.blockAttributes, ...props};
    this.blockAttributes.configAttributes = sanitizeBlockAttributes(this.blockAttributes.configAttributes)
    this.initBlock()
  }


  initBlock() {


    let Compon = (
      <div>test</div>
    )

    registerBlockType(this.blockAttributes.blockKey, {
      // Block Title
      title: this.blockAttributes.blockTitle,
      // Block Description
      description: this.blockAttributes.blockDescription,
      // Block Category
      category: this.blockAttributes.blockCategory,
      // Block Icon
      icon: this.blockAttributes.blockIcon,
      // Block Keywords
      keywords: this.blockAttributes.keywords,
      attributes: this.blockAttributes.configAttributes,
      // Defining the edit interface
      edit: editProps => {
        const {
          attributes
        } = editProps;

        const {configAttributes, sidenoteReplaces} = this.blockAttributes;

        let uploadButtonLabel = __('Upload');

        if (editProps.attributes.dzsap_meta_item_source || editProps.attributes.source) {
          uploadButtonLabel = __('Select another upload');
        }

        function onlyUnique(value, index, self) {
          if (value !== undefined) {
            return self.indexOf(value) === index;
          }
        }

        const categories = Object.keys(configAttributes).map(key => {
          return configAttributes[key].category;
        }).filter(onlyUnique);


        return [
          !!editProps.isSelected && (
            <InspectorControls key="inspector">
              {categories.map((categoryName) => {
                let controlsForCategory = Object.keys(configAttributes).map((configOptionKey) => {
                  if (configAttributes[configOptionKey].category === categoryName) {
                    return {...configAttributes[configOptionKey], ...{configOptionKey}};
                  }
                }).filter(function (element) {
                  return element !== undefined;
                });

                return categoryName ? (
                  <Panel title={categoryName}>
                    <PanelBody title={categoryName}>
                      <CustomInspectorControls
                        sidenoteReplaces={sidenoteReplaces}
                        configAttributes={controlsForCategory}
                        uploadButtonLabel={uploadButtonLabel}
                        {...editProps}
                      />
                    </PanelBody>
                  </Panel>
                ) : ''
              })}

            </InspectorControls>
          ),
          <div className={editProps.className}>
            <BlockFactoryContent
              sidenoteReplaces={sidenoteReplaces}
              configAttributes={configAttributes}
              uploadButtonLabel={uploadButtonLabel}
              attributes={editProps.attributes}
              setAttributes={editProps.setAttributes}
              adminPreviewComponent={this.blockAttributes.adminPreviewComponent}
            />
          </div>
        ];
      },

      save() {
        // -- Rendering in PHP
        return null;
      },
    });

  }

}