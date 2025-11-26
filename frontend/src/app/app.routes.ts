import { Routes } from '@angular/router';
import { Docentes } from './docentes/docentes';
import { Incidencias } from './incidencias/incidencias';
import { Dashboard } from './dashboard/dashboard';
import { Login } from './login/login';
import { AuthGuard } from './guards/auth.guard';

export const routes: Routes = [
  { path: 'login', component: Login },
  { 
    path: 'dashboard', 
    component: Dashboard, 
    canActivate: [AuthGuard] 
  },
  { 
    path: 'docentes', 
    component: Docentes, 
    canActivate: [AuthGuard],
    data: { roles: ['admin', 'academia', 'coordinador'] }
  },
  { 
    path: 'incidencias', 
    component: Incidencias, 
    canActivate: [AuthGuard],
    data: { roles: ['admin', 'academia', 'coordinador'] }
  },
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },
  { path: '**', redirectTo: '/dashboard' }
];
