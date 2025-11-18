import { Routes } from '@angular/router';
import { Docentes } from './docentes/docentes';
import { Incidencias } from './incidencias/incidencias';
import { Dashboard } from './dashboard/dashboard';

export const routes: Routes = [
  { path: 'docentes', component: Docentes },
  { path: 'incidencias', component: Incidencias },
  { path: 'dashboard', component: Dashboard },
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' }
];
