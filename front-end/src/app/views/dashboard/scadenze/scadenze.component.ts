import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-scadenze',
  templateUrl: './scadenze.component.html',
  styleUrls: ['./scadenze.component.css']
})
export class ScadenzeComponent implements OnInit {

  @Input() expirations: any;
  @Input() modalConfirmArgs: any;

  constructor() { }

  ngOnInit(): void {
  }

  modalConfirm(args: any) {
    this.modalConfirmArgs = args;
  }

  modalConfirmSuccess(e: any) {
    console.log(e);
  }

}
