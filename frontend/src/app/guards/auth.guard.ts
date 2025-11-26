import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {
  constructor(
    private router: Router,
    private authService: AuthService
  ) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    if (this.authService.isAuthenticated()) {
      // Verificar roles si están especificados en la ruta
      const requiredRoles = route.data['roles'] as string[];
      
      if (requiredRoles && requiredRoles.length > 0) {
        if (!this.authService.hasRole(requiredRoles)) {
          // Usuario no tiene el rol requerido
          this.router.navigate(['/unauthorized']);
          return false;
        }
      }
      
      return true;
    }

    // No está autenticado, redirigir al login
    this.router.navigate(['/login'], { queryParams: { returnUrl: state.url } });
    return false;
  }
}
