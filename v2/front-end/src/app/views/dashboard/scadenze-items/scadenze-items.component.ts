import {Component, Input, OnInit} from '@angular/core';
import { faSync, faAt, faFileInvoiceDollar } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'tbody[app-scadenze-items]',
  templateUrl: './scadenze-items.component.html',
  styleUrls: ['./scadenze-items.component.css']
})
export class ScadenzeItemsComponent implements OnInit {

  @Input() expiration: any;

  faSync = faSync;
  faAt = faAt;
  faFileInvoiceDollar = faFileInvoiceDollar;
  isCollapsed = true;

  constructor() { }

  ngOnInit(): void {
  }

}
