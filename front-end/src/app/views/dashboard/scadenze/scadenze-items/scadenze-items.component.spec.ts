import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ScadenzeItemsComponent } from './scadenze-items.component';

describe('ScadenzeItemsComponent', () => {
  let component: ScadenzeItemsComponent;
  let fixture: ComponentFixture<ScadenzeItemsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ ScadenzeItemsComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(ScadenzeItemsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
