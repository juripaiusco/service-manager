import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import { faSync, faAt, faFileInvoiceDollar } from '@fortawesome/free-solid-svg-icons';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';

@Component({
  selector: 'tbody[app-scadenze-items]',
  templateUrl: './scadenze-items.component.html',
  styleUrls: ['./scadenze-items.component.css']
})
export class ScadenzeItemsComponent implements OnInit {

  @Input() expiration: any;
  @Output('onServiceActionEvent') serviceActionEmit: EventEmitter<any> = new EventEmitter<any>()
  @Output('onGenerateInvoice') eventGenerateInvoice: EventEmitter<any> = new EventEmitter<any>()

  faSync = faSync;
  faAt = faAt;
  faFileInvoiceDollar = faFileInvoiceDollar;
  isCollapsed = true;

  modalRef: any;

  constructor(private modalService: BsModalService) { }

  ngOnInit(): void {
  }

  serviceAction(args: any) {
    this.serviceActionEmit.emit(args);
  }

  openModalConfirmFormInvoice(template: any) {
    this.modalRef = this.modalService.show(
      template,
      Object.assign({}, { class: 'modal-dialog modal-dialog-centered' })
    );
  }

  generateInvoice(e: any) {
    this.eventGenerateInvoice.emit(e)
  }

}
