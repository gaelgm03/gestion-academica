import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-unauthorized',
  imports: [CommonModule],
  template: `
    <div class="unauthorized-container">
      <div class="unauthorized-card">
        <div class="icon-container">
          <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
          </svg>
        </div>
        
        <h1>Acceso Denegado</h1>
        <p class="message">No tienes permisos para acceder a esta página.</p>
        
        <div class="user-info" *ngIf="userName">
          <p>Usuario: <strong>{{ userName }}</strong></p>
          <p>Rol: <strong>{{ userRole }}</strong></p>
        </div>
        
        <div class="actions">
          <button class="btn-primary" (click)="goToDashboard()">
            Ir al Dashboard
          </button>
          <button class="btn-secondary" (click)="logout()">
            Cerrar Sesión
          </button>
        </div>
        
        <p class="help-text">
          Si crees que deberías tener acceso, contacta al administrador del sistema.
        </p>
      </div>
    </div>
  `,
  styles: [`
    .unauthorized-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 20px;
    }
    
    .unauthorized-card {
      background: white;
      border-radius: 16px;
      padding: 40px;
      max-width: 450px;
      width: 100%;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .icon-container {
      color: #e74c3c;
      margin-bottom: 20px;
    }
    
    h1 {
      color: #333;
      font-size: 28px;
      margin: 0 0 10px 0;
    }
    
    .message {
      color: #666;
      font-size: 16px;
      margin-bottom: 25px;
    }
    
    .user-info {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 25px;
    }
    
    .user-info p {
      margin: 5px 0;
      color: #555;
      font-size: 14px;
    }
    
    .actions {
      display: flex;
      gap: 15px;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    
    button {
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-secondary {
      background: #f8f9fa;
      color: #333;
      border: 1px solid #ddd;
    }
    
    .btn-secondary:hover {
      background: #e9ecef;
    }
    
    .help-text {
      color: #999;
      font-size: 12px;
      margin: 0;
    }
  `]
})
export class Unauthorized {
  userName: string | null = null;
  userRole: string | null = null;

  constructor(
    private router: Router,
    private authService: AuthService
  ) {
    const user = this.authService.currentUserValue;
    if (user) {
      this.userName = user.nombre;
      this.userRole = user.rol_nombre;
    }
  }

  goToDashboard() {
    this.router.navigate(['/dashboard']);
  }

  logout() {
    this.authService.logout().subscribe({
      complete: () => {
        this.router.navigate(['/login']);
      }
    });
  }
}
