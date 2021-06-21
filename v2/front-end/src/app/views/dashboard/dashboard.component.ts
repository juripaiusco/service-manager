import {Component, Inject, OnInit} from '@angular/core';
import { DashboardService } from "../../services/dashboard.service";

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {

  expirations: any;

  constructor(private dashboardService: DashboardService) { }

  ngOnInit(): void {
    this.expirations = this.dashboardService.getExpiration()
  }

}
