import { Routes } from '@angular/router';
import { Docentes } from './docentes/docentes';
import { Incidencias } from './incidencias/incidencias';
import { Cursos } from './cursos/cursos';
import { Evaluaciones } from './evaluaciones/evaluaciones';
import { ReporteMaterias } from './reporte-materias/reporte-materias';
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
  { 
    path: 'cursos', 
    component: Cursos, 
    canActivate: [AuthGuard],
    data: { roles: ['admin', 'academia', 'coordinador'] }
  },
  { 
    path: 'evaluaciones', 
    component: Evaluaciones, 
    canActivate: [AuthGuard],
    data: { roles: ['admin', 'academia', 'coordinador'] }
  },
  { 
    path: 'reporte-materias', 
    component: ReporteMaterias, 
    canActivate: [AuthGuard],
    data: { roles: ['admin', 'academia', 'direccion', 'coordinador'] }
  },
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },
  { path: '**', redirectTo: '/dashboard' }
];
