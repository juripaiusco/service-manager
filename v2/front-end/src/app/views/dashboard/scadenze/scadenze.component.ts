import { Component, OnInit, Input } from '@angular/core';
import { faSync, faAt, faFileInvoiceDollar } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'app-scadenze',
  templateUrl: './scadenze.component.html',
  styleUrls: ['./scadenze.component.css']
})
export class ScadenzeComponent implements OnInit {

  faSync = faSync;
  faAt = faAt;
  faFileInvoiceDollar = faFileInvoiceDollar;

  @Input() expirations: any;

  constructor() { }

  ngOnInit(): void {
  }

}
