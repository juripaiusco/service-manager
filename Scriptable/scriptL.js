// Variables used by Scriptable.
// These must be at the very top of the file. Do not edit.
// icon-color: blue; icon-glyph: money-bill-alt;
const widget = await createWidget()

if (!config.runsInWidget)
{
    await widget.presentLarge()
}

Script.setWidget(widget)
Script.complete()

async function createWidget(items)
{
    switch(Device.model()) {
        case 'iPad':
            stackWidth = 148
            spacer = 0
            widgetBgColor = '#1e1e1e'
            break;
        case 'iPhone':
            stackWidth = 158
            spacer = 14
            widgetBgColor = '#2c2c2e'
            break;
    }

    // Configuration
    let config = {
        'stackWidth': stackWidth,
        'spacer': spacer,
        'widgetBgColor': widgetBgColor
    }

    // -------------------------------------------------------

    /**
     * Read JSON data
     * @type {Request}
     */
    let r = new Request('WRITE_YOUR_URL')
    let json = await r.loadJSON()

    // -------------------------------------------------------

    /**
     * Init Widget
     * @type {ListWidget}
     */
    let widget = new ListWidget()
    widget.backgroundColor = Color.dynamic(Color.white(), new Color(widgetBgColor))

    // -------------------------------------------------------
    // Creazione stack HEADER

    let stackHeader = widget.addStack()

    // Creazione stack Header LEFT
    let stackHeaderLeft = stackHeaderElement(stackHeader, {
        'value':        '€\u00a0' + json.entrate_anno_vs.value,
        'valueColor':   json.entrate_anno_vs.sign == '-' ? Color.red() : Color.green(),
        'icon':         '▼',
        'elementColor': Color.green(),
        config
    });

    stackHeader.addSpacer(15)

    // Creazione stack Header RIGHT
    let stackHeaderRight = stackHeaderElement(stackHeader, {
        'value':        '€\u00a0' + json.uscite_anno_vs.value,
        'valueColor':   json.uscite_anno_vs.sign == '-' ? Color.green() : Color.red(),
        'icon':         '▲',
        'elementColor': Color.red(),
        config
    });
    // -------------------------------------------------------


    // -------------------------------------------------------
    // Creazione andamento Trimestre
    widget.addSpacer(15 + config['spacer'])

    let textTrimestreValue = widget.addText(json.trimestre.value)
    textTrimestreValue.font = Font.title2()
    textTrimestreValue.centerAlignText()

    widget.addSpacer(5)

    let textTrimestreLegend = widget.addText('utile ' + json.trimestre.periodo + '° trimestre')
    textTrimestreLegend.font = Font.systemFont(12)
    textTrimestreLegend.centerAlignText()

    widget.addSpacer(15 + config['spacer'])
    // -------------------------------------------------------


    // -------------------------------------------------------
    // Creazione stack BODY
    let stackBody = widget.addStack()

    // Creazione stack Body LEFT
    let stackBodyLeft = stackBodyElement(stackBody, {
        'value': json.entrate_mese_corrente.value,
        'valueComparison': '€ ' + json.entrate_mese_corrente.vs_sign + json.entrate_mese_corrente.vs,
        'elementColor': Color.green(),
        config
    })

    stackBody.addSpacer(15)

    // Creazione stack Body RIGHT
    let stackBodyRight = stackBodyElement(stackBody, {
        'value': json.uscite_mese_corrente.value,
        'valueComparison': '€ ' + json.uscite_mese_corrente.vs_sign + json.uscite_mese_corrente.vs,
        'elementColor': Color.red(),
        config
    })
    // -------------------------------------------------------


    // -------------------------------------------------------
    // Creazione stack FOOTER
    widget.addSpacer(15)

    let stackFooter = widget.addStack()

    // Creazione stack Footer LEFT
    let stackFooterLeft = stackFooterElement(stackFooter, {
        'items': json.category_positive,
        'elementColor': Color.green(),
        config
    })

    stackFooter.addSpacer(15)

    // Creazione stack Header RIGHT
    let stackFooterRight = stackFooterElement(stackFooter, {
        'items': json.category_negative,
        'elementColor': Color.red(),
        config
    })
    // -------------------------------------------------------


    /**
     * Set refresh
     * @type {number}
     */
    let interval = 1000 * 60 * 60 * 1
    widget.refreshAfterDate = new Date(Date.now() + interval)

    return widget
}

function stackHeaderElement(ObjStack, args)
{
    let stack = ObjStack.addStack()
    stack.borderWidth = 2
    stack.cornerRadius = 6
    stack.borderColor = args['elementColor']
    stack.size = new Size(args['config']['stackWidth'], 0)
    stack.setPadding(5, 10, 5, 10)

    // Icon Stack
    let iconStack = stack.addStack();
    let iconRight = iconStack.addText(args['icon'] + '                                                     ')
    iconRight.font = Font.systemFont(12)
    iconRight.textColor = args['elementColor']

    // Text Stack
    let textStack = stack.addStack();
    let text = textStack.addText(args['value'])
    text.font = Font.systemFont(12)
    text.textColor = args['valueColor']

    return stack
}

function stackBodyElement(ObjStack, args)
{
    let stack = ObjStack.addStack()
    stack.size = new Size(args['config']['stackWidth'], 0)
    stack.cornerRadius = 10
    stack.backgroundColor = args['elementColor']
    stack.setPadding(10, 10, 10, 10)
    stack.layoutVertically()

    // text value
    let textValue = stack.addText(args['value'])
    textValue.font = Font.boldSystemFont(16)
    textValue.textColor = Color.white()

    stack.addSpacer(5)

    // text comparison
    let textComparison = stack.addText('( ' + args['valueComparison'] + ' )')
    textComparison.font = Font.systemFont(12)
    textComparison.textColor = Color.white()

    return stack
}

function stackFooterElement(ObjStack, args)
{
    let stack = ObjStack.addStack()
    stack.borderWidth = 2
    stack.cornerRadius = 6
    stack.borderColor = args['elementColor']
    stack.size = new Size(args['config']['stackWidth'], 145)
    stack.setPadding(5, 5, 5, 5)
    stack.layoutVertically()

    for (var i = 0; i < 9; i++) {

        // Item Stack
        let stackItem = stack.addStack();

        // Category name Stack
        let catNameStack = stackItem.addStack();

        let name = ' ';
        let value = ' ';

        if (typeof args['items'][i] !== 'undefined') {
            name = args['items'][i].name;
            value = '€\u00a0' + args['items'][i].value;
        }

        let textCatNameLeft = catNameStack.addText(name + '                                                     ')
        textCatNameLeft.font = Font.systemFont(12)
        textCatNameLeft.textColor = args['elementColor']

        // Valute category Stack
        let catValueStack = stackItem.addStack();

        let textCatValueRight = catValueStack.addText(value)
        textCatValueRight.font = Font.systemFont(12)
        textCatValueRight.textColor = args['elementColor']

    }
}
