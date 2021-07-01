import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DashboardService {

  constructor() { }

  getExpiration() {

    return [{
      'id': 1,
      'company': 'Rigoni Macchine Utensili S.r.l.',
      'name': 'Dario',
      'email': 'dario@ottomatite.com',
      'piva': '',
      'service': 'Hosting',
      'service_reference': 'ottomatite.com',
      'date_expiration': new Date(2020, 10, 25, 0, 0, 0),
      'expiration': 'expired',
      'price': '125',
      'price_tax': '152.5',
      'service_details': [{
        'name': 'Dominio .com (Shellrent)',
        'reference': 'ottomatite.com',
        'price': '15'
      }, {
        'name': 'Hosting Mail (Shellrent)',
        'reference': 'dario@ottomatite.com; dieci@ottomatite.com; due@ottomatite.com; fiamma@ottomatite.com; info@ottomatite.com',
        'price': '85'
      }]
    }, {
      'id': 2,
      'company': 'Mr. J di Juri Paiusco',
      'name': 'Juri',
      'email': 'juri@mr-j.it',
      'piva': '',
      'service': 'Hosting',
      'service_reference': 'mr-j.it',
      'date_expiration': new Date(2020, 11, 7, 0, 0, 0),
      'expiration': 'expired',
      'price': '0',
      'price_tax': '0',
      'service_details': [{
        'name': 'Dominio .it (dominiofaidate.it)',
        'reference': 'mr-j.it',
        'price': '0'
      }, {
        'name': 'Hosting Mail (Shellrent)',
        'reference': 'mr-j.it',
        'price': '0'
      }]
    }, {
      'id': 3,
      'company': 'Eco-Trans S.R.L.',
      'name': 'Sonia',
      'email': 'sonia@ecotrans.it',
      'piva': '',
      'service': 'Hosting',
      'service_reference': 'ecotrans.it',
      'date_expiration': new Date(2021, 8, 5, 0, 0, 0),
      'expiration': 'in_expiration',
      'price': '80',
      'price_tax': '97.6'
    }, {
      'id': 4,
      'company': 'Viprof S.r.l. ',
      'name': 'dott. Manea',
      'email': 'amministrazione@viprof.com',
      'piva': '',
      'service': 'Certificato SSL',
      'service_reference': 'tsgw.viprof.com',
      'date_expiration': new Date(2021, 9, 16, 0, 0, 0),
      'expiration': '',
      'price': '54',
      'price_tax': '65.88'
    }];

  }

}
