export function __currency(data, currency) {

    let currencyFormat;

    switch (currency) {

        case 'EUR':

            currencyFormat = Intl.NumberFormat('it', {
                style: 'currency',
                currency: 'EUR',
            });

            break;

    }

    return currencyFormat.format(data);

}
