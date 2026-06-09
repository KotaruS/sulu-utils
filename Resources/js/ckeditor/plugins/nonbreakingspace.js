
import { Plugin, Command } from '@ckeditor/ckeditor5-core'
import { ButtonView } from '@ckeditor/ckeditor5-ui'
import { translate } from 'sulu-admin-bundle/utils'
import { toMap } from '@ckeditor/ckeditor5-utils'

// $FlowFixMe
import spaceIcon from '../nbsp.svg' // eslint-disable-line import/no-webpack-loader-syntax

export default class NonBreakingSpace extends Plugin {
  init() {
    const editor = this.editor

    editor.commands.add('insertnbsp', new NonBreakingSpaceCommand(editor))

    editor.keystrokes.set('Ctrl+Alt+X', 'insertnbsp')

    editor.ui.componentFactory.add('nbsp', (locale) => {
      const button = new ButtonView(locale)
      button.set({
        label: translate('sulu_utils.nbsp_editor'),
        icon: spaceIcon,
        tooltip: true,
        keystroke: 'Ctrl+Alt+X',
      })

      this.listenTo(button, 'execute', () => {
        editor.execute('insertnbsp')
      })

      return button
    })
  }

  static get pluginName() {
    return 'NonBreakingSpace'
  }
}

class NonBreakingSpaceCommand extends Command {
  execute() {
    const model = this.editor.model
    const selection = model.document.selection

    model.change(writer => {
      const firstPosition = selection.getFirstPosition()
      const attributes = toMap(selection.getAttributes())
      const { end: positionAfter } = model.insertContent(
        writer.createText(' ', attributes)
      )

      // Put the selection at the end of the inserted abbreviation.
      writer.setSelection(positionAfter)

    })
  }
}
