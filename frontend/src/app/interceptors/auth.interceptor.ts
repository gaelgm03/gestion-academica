import { Injectable } from '@angular/core';
import { HttpRequest, HttpHandler, HttpEvent, HttpInterceptor, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError, BehaviorSubject } from 'rxjs';
import { catchError, filter, take, switchMap } from 'rxjs/operators';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  private isRefreshing = false;
  private refreshTokenSubject: BehaviorSubject<any> = new BehaviorSubject<any>(null);

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    // Agregar token a la petición si existe
    const token = this.authService.token;
    
    if (token) {
      request = this.addToken(request, token);
    }

    return next.handle(request).pipe(
      catchError((error: HttpErrorResponse) => {
        if (error.status === 401) {
          // Token expirado, intentar refrescar
          return this.handle401Error(request, next);
        }
        
        if (error.status === 403) {
          // Sin permisos
          console.error('Acceso denegado:', error.error);
          alert('No tienes permisos para realizar esta acción');
        }

        return throwError(() => error);
      })
    );
  }

  private addToken(request: HttpRequest<any>, token: string): HttpRequest<any> {
    return request.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
  }

  private handle401Error(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (!this.isRefreshing) {
      this.isRefreshing = true;
      this.refreshTokenSubject.next(null);

      return this.authService.refreshToken().pipe(
        switchMap((response: any) => {
          this.isRefreshing = false;
          this.refreshTokenSubject.next(response.data.access_token);
          return next.handle(this.addToken(request, response.data.access_token));
        }),
        catchError((err) => {
          this.isRefreshing = false;
          
          // Refresh token también expiró, redirigir a login
          this.authService.logout().subscribe();
          this.router.navigate(['/login']);
          
          return throwError(() => err);
        })
      );
    } else {
      // Esperar a que se complete el refresh
      return this.refreshTokenSubject.pipe(
        filter(token => token != null),
        take(1),
        switchMap(token => {
          return next.handle(this.addToken(request, token));
        })
      );
    }
  }
}
