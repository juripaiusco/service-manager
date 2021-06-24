import { Component, OnInit, Input } from '@angular/core';

@Component({
  selector: 'app-scadenze',
  templateUrl: './scadenze.component.html',
  styleUrls: ['./scadenze.component.css']
})
export class ScadenzeComponent implements OnInit {

  @Input() expirations: any;

  constructor() { }

  ngOnInit(): void {
  }

}
