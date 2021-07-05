import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ModalConfirmFormInvoiceComponent } from './modal-confirm-form-invoice.component';

describe('ModalConfirmFormInvoiceComponent', () => {
  let component: ModalConfirmFormInvoiceComponent;
  let fixture: ComponentFixture<ModalConfirmFormInvoiceComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ModalConfirmFormInvoiceComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ModalConfirmFormInvoiceComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
