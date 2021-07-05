import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';

@Component({
  selector: 'app-modal-confirm-form-invoice',
  templateUrl: './modal-confirm-form-invoice.component.html',
  styleUrls: ['./modal-confirm-form-invoice.component.css']
})
export class ModalConfirmFormInvoiceComponent implements OnInit {

  @Input() InvoiceArgs: any;
  @Output('onClicCancel') eventCancel: EventEmitter<any> = new EventEmitter<any>()
  @Output('onClicGenerateInvoice') eventGenerateInvoice: EventEmitter<any> = new EventEmitter<any>()

  invoiceDate = Date.now();

  constructor() { }

  ngOnInit(): void {
  }

  closeModal() {
    this.eventCancel.emit(true);
  }

  invoiceGenerate(customerID: any) {
    this.eventGenerateInvoice.emit({
      'action': 'invoiceGenerate',
      'id': customerID
    });
    this.eventCancel.emit(true);
  }

}
