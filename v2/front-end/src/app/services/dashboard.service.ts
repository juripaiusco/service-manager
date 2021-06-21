import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class DashboardService {

  constructor() { }

  getExpiration() {

    return [{
      'company': 'Rigoni Macchine Utensili S.r.l.',
      'name': 'Dario',
      'email': 'dario@ottomatite.com',
      'piva': '',
      'service': 'Hosting',
      'service_reference': 'ottomatite.com',
      'expiration': '25/10/2020',
      'price': '125',
      'price_tax': '152.5',
      'service_details': [{

      }]
    }, {
      'company': 'Mr. J di Juri Paiusco',
      'name': 'Juri',
      'email': 'juri@mr-j.it',
      'piva': '',
      'service': 'Hosting',
      'service_reference': 'mr-j.it',
      'expiration': '07/11/2020',
      'price': '0',
      'price_tax': '0'
    }];

  }

}
