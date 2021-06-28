import { LOCALE_ID, NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import '@angular/common/locales/global/it';

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
import { ScadenzeItemsComponent } from './views/dashboard/scadenze/scadenze-items/scadenze-items.component';

import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { CollapseModule } from 'ngx-bootstrap/collapse';
import { ModalModule } from 'ngx-bootstrap/modal';
import { ModalConfirmComponent } from './components/modal-confirm/modal-confirm.component';

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
    ScadenzeComponent,
    ScadenzeItemsComponent,
    ModalConfirmComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    FontAwesomeModule,
    RouterModule.forRoot(routes),
    BrowserAnimationsModule,
    CollapseModule.forRoot(),
    ModalModule.forRoot()
  ],
  providers: [{ provide: LOCALE_ID, useValue: 'it' }],
  bootstrap: [AppComponent]
})
export class AppModule { }
