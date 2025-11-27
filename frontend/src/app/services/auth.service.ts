import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, BehaviorSubject, tap } from 'rxjs';

const API_URL = 'http://localhost/gestion_academica/backend';

export interface User {
  id: number;
  email: string;
  nombre: string;
  rol_id: number;
  rol_nombre: string;
  docente_id?: number;
  docente_estatus?: string;
}

export interface AuthResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    access_token: string;
    refresh_token: string;
    expires_in: number;
  };
}

export interface MeResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    permissions: any;
    docente_info?: any;
  };
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private currentUserSubject: BehaviorSubject<User | null>;
  public currentUser: Observable<User | null>;
  private tokenKey = 'access_token';
  private refreshTokenKey = 'refresh_token';

  constructor(private http: HttpClient) {
    const storedUser = localStorage.getItem('current_user');
    this.currentUserSubject = new BehaviorSubject<User | null>(
      storedUser ? JSON.parse(storedUser) : null
    );
    this.currentUser = this.currentUserSubject.asObservable();
  }

  public get currentUserValue(): User | null {
    return this.currentUserSubject.value;
  }

  public get token(): string | null {
    return localStorage.getItem(this.tokenKey);
  }

  login(email: string, password: string): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${API_URL}/api/auth.php?action=login`, {
      email,
      password
    }).pipe(
      tap(response => {
        if (response.success && response.data) {
          // Guardar tokens
          localStorage.setItem(this.tokenKey, response.data.access_token);
          localStorage.setItem(this.refreshTokenKey, response.data.refresh_token);
          localStorage.setItem('current_user', JSON.stringify(response.data.user));
          
          // Actualizar usuario actual
          this.currentUserSubject.next(response.data.user);
        }
      })
    );
  }

  logout() {
    // Limpiar localStorage
    localStorage.removeItem(this.tokenKey);
    localStorage.removeItem(this.refreshTokenKey);
    localStorage.removeItem('current_user');
    
    // Actualizar usuario actual
    this.currentUserSubject.next(null);
    
    // Llamar al endpoint de logout (opcional)
    return this.http.post(`${API_URL}/api/auth.php?action=logout`, {});
  }

  refreshToken(): Observable<any> {
    const refreshToken = localStorage.getItem(this.refreshTokenKey);
    
    if (!refreshToken) {
      return new Observable(observer => {
        observer.error('No refresh token available');
      });
    }

    return this.http.post<any>(`${API_URL}/api/auth.php?action=refresh`, {
      refresh_token: refreshToken
    }).pipe(
      tap(response => {
        if (response.success && response.data) {
          localStorage.setItem(this.tokenKey, response.data.access_token);
        }
      })
    );
  }

  me(): Observable<MeResponse> {
    return this.http.get<MeResponse>(`${API_URL}/api/auth.php?action=me`);
  }

  checkAuth(): Observable<any> {
    return this.http.get(`${API_URL}/api/auth.php?action=check`);
  }

  isAuthenticated(): boolean {
    return !!this.token && !!this.currentUserValue;
  }

  hasRole(roles: string[]): boolean {
    const user = this.currentUserValue;
    if (!user) return false;
    return roles.includes(user.rol_nombre);
  }

  hasPermission(scope: string, action: string): boolean {
    const user = this.currentUserValue;
    if (!user) return false;
    
    // Los administradores tienen todos los permisos
    if (user.rol_nombre === 'admin') return true;
    
    // Matriz de permisos por rol (sincronizada con BD rol_permiso)
    const permisosRol: { [rol: string]: { [scope: string]: string[] } } = {
      'academia': {
        'docente': ['ver'],
        'incidencia': ['registrar', 'actualizar', 'ver'],
        'reporte': ['exportar', 'ver'],
        'academia': ['gestionar']
      },
      'direccion': {
        'docente': ['ver'],
        'incidencia': ['ver'],
        'reporte': ['exportar', 'ver']
      },
      'docente': {
        'docente': ['ver'],
        'incidencia': ['registrar', 'ver']
      },
      'coordinador': {
        'docente': ['crear', 'editar', 'ver'],
        'incidencia': ['registrar', 'actualizar', 'ver'],
        'reporte': ['exportar', 'ver'],
        'academia': ['gestionar']
      }
    };
    
    const permisos = permisosRol[user.rol_nombre];
    if (!permisos) return false;
    
    const scopePermisos = permisos[scope];
    if (!scopePermisos) return false;
    
    return scopePermisos.includes(action);
  }

  /**
   * Verifica si el usuario tiene al menos uno de los roles especificados
   */
  hasAnyRole(roles: string[]): boolean {
    const user = this.currentUserValue;
    if (!user) return false;
    return roles.includes(user.rol_nombre);
  }
}
