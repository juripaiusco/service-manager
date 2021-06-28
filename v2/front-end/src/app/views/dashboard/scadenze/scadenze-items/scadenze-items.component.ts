import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import { faSync, faAt, faFileInvoiceDollar } from '@fortawesome/free-solid-svg-icons';

@Component({
  selector: 'tbody[app-scadenze-items]',
  templateUrl: './scadenze-items.component.html',
  styleUrls: ['./scadenze-items.component.css']
})
export class ScadenzeItemsComponent implements OnInit {

  @Input() expiration: any;
  @Output('onUpdateService') serviceUpdate = new EventEmitter();

  faSync = faSync;
  faAt = faAt;
  faFileInvoiceDollar = faFileInvoiceDollar;
  isCollapsed = true;

  constructor() { }

  ngOnInit(): void {
  }

  serviceRenew(args: any) {
    this.serviceUpdate.emit(args);
  }

}
