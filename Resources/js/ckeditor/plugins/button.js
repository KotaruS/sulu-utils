
import { Plugin, Command } from '@ckeditor/ckeditor5-core'
import { ButtonView } from '@ckeditor/ckeditor5-ui'
import { translate } from 'sulu-admin-bundle/utils'
import './buttonStyles.css'
import { priorities } from '@ckeditor/ckeditor5-utils';
// $FlowFixMe
import Icon from '../btn.svg' // eslint-disable-line import/no-webpack-loader-syntax
import { GeneralHtmlSupport } from '@ckeditor/ckeditor5-html-support';

const BUTTON = 'htmlButton'

export default class Button extends Plugin {
  constructor(editor) {
    super(editor);
    editor.config.define('button', {
      options: []
    })
  }
  requires() {
    return [GeneralHtmlSupport];
  }
  init() {
    const editor = this.editor
    const dataFilter = this.editor.plugins.get('DataFilter');
    const dataSchema = this.editor.plugins.get('DataSchema');

    // need to do code gymnastics just to change the faulty config of the GeneralHtmlSupport button
    dataSchema.extendInlineElement({
      model: 'htmlButton',
      view: 'button',
      isObject: false,
      modelSchema: null,
      attributeProperties: {
        copyOnEnter: true,
        isFormatting: true
      }
    })
    dataFilter.allowElement('button')
    editor.data.on('init', () => {
      dataFilter._allowedElements.forEach((el) => {
        if (el.view === 'button' && el.isObject === true) {
          dataFilter._allowedElements.delete(el)
        }
      })
    }, { priority: priorities.highest + 2 })

    // Dont need this since GeneralHtmlSupport adds it for us
    // editor.model.schema.extend('$text', { allowAttributes: BUTTON });
    // editor.conversion.attributeToElement({
    //   model: BUTTON,
    //   view: 'button',
    // });


    editor.commands.add(BUTTON, new ButtonCommand(editor, BUTTON));

    const command = editor.commands.get(BUTTON)

    editor.ui.componentFactory.add('button', (locale) => {
      const button = new ButtonView(locale)
      button.set({
        label: translate('sulu_utils.button_element'),
        icon: Icon,
        tooltip: true,
      })

      button.bind('isEnabled').to(command, 'isEnabled');
      button.bind('isOn').to(command, 'value');

      this.listenTo(button, 'execute', () => {
        editor.execute(BUTTON);
        editor.editing.view.focus();
      })

      return button
    })
  }

  static get pluginName() {
    return 'Button'
  }
}

class ButtonCommand extends Command {
  refresh() {
    const model = this.editor.model;
    const doc = model.document;

    this.value = this._getValueFromFirstAllowedNode();
    this.isEnabled = model.schema.checkAttributeInSelection(doc.selection, BUTTON);
  }

  execute(options = {}) {
    const model = this.editor.model;
    const doc = model.document;
    const selection = doc.selection;
    const value = !this.value;

    model.change(writer => {
      if (selection.isCollapsed) {
        if (value) {
          writer.setSelectionAttribute(BUTTON, true);
        } else {
          writer.removeSelectionAttribute(BUTTON);
        }
      } else {
        const ranges = model.schema.getValidRanges(selection.getRanges(), BUTTON);

        for (const range of ranges) {
          if (value) {
            writer.setAttribute(BUTTON, value, range);
          } else {
            writer.removeAttribute(BUTTON, range);
          }
        }
      }
    });
  }

  _getValueFromFirstAllowedNode() {
    const model = this.editor.model;
    const schema = model.schema;
    const selection = model.document.selection;

    if (selection.isCollapsed) {
      return selection.hasAttribute(BUTTON);
    }

    for (const range of selection.getRanges()) {
      for (const item of range.getItems()) {
        if (schema.checkAttribute(item, BUTTON)) {
          return item.hasAttribute(BUTTON);
        }
      }
    }

    return false;
  }
}
