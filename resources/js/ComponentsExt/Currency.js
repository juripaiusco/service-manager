export function __currency(data, currency) {

    let currencyFormat;

    switch (currency) {

        case 'EUR':

            currencyFormat = Intl.NumberFormat('en', {
                style: 'currency',
                currency: 'EUR',
            });

            break;

    }

    return currencyFormat.format(data);

}
