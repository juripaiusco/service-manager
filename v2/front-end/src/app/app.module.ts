import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NavbarComponent } from './header/navbar/navbar.component';
import { BreadcrumbComponent } from './header/breadcrumb/breadcrumb.component';

import { RouterModule, Routes } from "@angular/router";
import { DashboardComponent } from './views/dashboard/dashboard.component';
import { ServiceComponent } from './views/service/service.component';
import { CustomerComponent } from './views/customer/customer.component';
import { HeaderComponent } from './header/header.component';
import { ScadenzeComponent } from './views/dashboard/scadenze/scadenze.component';

const routes: Routes = [{
  path: 'dashboard',
  component: DashboardComponent
}, {
  path: 'service',
  component: ServiceComponent
}, {
  path: 'customer',
  component: CustomerComponent
}, {
  path: '',
  redirectTo: '/dashboard',
  pathMatch: 'full'
}]

@NgModule({
  declarations: [
    AppComponent,
    NavbarComponent,
    BreadcrumbComponent,
    DashboardComponent,
    ServiceComponent,
    CustomerComponent,
    HeaderComponent,
    ScadenzeComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FontAwesomeModule,
    RouterModule.forRoot(routes)
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
