import {Component, EventEmitter, Input, OnInit, OnChanges, Output} from '@angular/core';
import { BsModalService, BsModalRef } from 'ngx-bootstrap/modal';

@Component({
  selector: 'app-modal-confirm',
  template: ''
})
export class ModalConfirmComponent implements OnInit {

  @Input() modalConfirmArgs: any;
  @Output('onClicBtnSuccess') eventModalConfirm: EventEmitter<any> = new EventEmitter();

  bsModalRef: any;

  constructor(private modalService: BsModalService) { }

  ngOnInit(): void { }

  ngOnChanges() {
    this.openModalConfirm(this.modalConfirmArgs)
  }

  openModalConfirm(args: any) {

    if (args?.title) {

      this.bsModalRef = this.modalService.show(
        ModalContentComponent,
        Object.assign({}, { class: 'modal-dialog modal-dialog-centered' })
      );

      this.bsModalRef.content.args = args;

      this.bsModalRef.content.event.subscribe((d: any) => {
        this.onClicBtnSuccess(d.data);
      });

    }

  }

  onClicBtnSuccess(data: any) {
    this.eventModalConfirm.emit(data);
  }

}

/* This is a component which we pass in modal*/
@Component({
  // eslint-disable-next-line @angular-eslint/component-selector
  selector: 'modal-content',
  template: `
    <div class="modal-header">
      <h5 class="modal-title">{{ args?.title }}</h5>

      <button type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
              (click)="bsModalRef.hide()"></button>

    </div>
    <div class="modal-body">

      <br>

      <p [innerHTML]="args?.desc" class="text-center"></p>

      <br>

      <div class="row">
        <div class="col-6 d-grid">

          <button type="button"
                  class="btn btn-success"
                  (click)="clickBtnSuccess()">{{ args?.btnSuccess }}</button>

        </div>
        <div class="col-6 d-grid">

          <button type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                  (click)="bsModalRef.hide()">{{ args?.btnClose }}</button>

        </div>
      </div>

    </div>
    <!--<div class="modal-footer"></div>-->
  `
})

export class ModalContentComponent implements OnInit {

  public event: EventEmitter<any> = new EventEmitter();

  args: any;
  list: any[] = [];

  constructor(public bsModalRef: BsModalRef) { }

  ngOnInit() { }

  clickBtnSuccess() {
    this.event.emit({ data: this.args?.btnSuccessReturn });
    this.bsModalRef.hide();
  }
}
