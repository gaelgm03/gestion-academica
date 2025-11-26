import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-login',
  imports: [CommonModule, FormsModule],
  templateUrl: './login.html',
  styleUrl: './login.css',
})
export class Login {
  email = '';
  password = '';
  loading = false;
  error: string | null = null;
  returnUrl = '/dashboard';
  
  // Validación
  formErrors: { [key: string]: string } = {};
  formTouched: { [key: string]: boolean } = {};
  formSubmitted = false;

  constructor(
    private authService: AuthService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    // Si ya está autenticado, redirigir al dashboard
    if (this.authService.isAuthenticated()) {
      this.router.navigate(['/dashboard']);
    }

    // Obtener la URL de retorno desde los query params
    this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/dashboard';
  }

  validateField(field: string): boolean {
    this.formTouched[field] = true;
    delete this.formErrors[field];

    switch (field) {
      case 'email':
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!this.email) {
          this.formErrors[field] = 'El email es requerido';
          return false;
        }
        if (!emailRegex.test(this.email)) {
          this.formErrors[field] = 'Ingresa un email válido';
          return false;
        }
        break;
      case 'password':
        if (!this.password) {
          this.formErrors[field] = 'La contraseña es requerida';
          return false;
        }
        if (this.password.length < 4) {
          this.formErrors[field] = 'La contraseña debe tener al menos 4 caracteres';
          return false;
        }
        break;
    }
    return true;
  }

  validateForm(): boolean {
    this.formSubmitted = true;
    let isValid = true;
    if (!this.validateField('email')) isValid = false;
    if (!this.validateField('password')) isValid = false;
    return isValid;
  }

  hasError(field: string): boolean {
    return !!(this.formErrors[field] && (this.formTouched[field] || this.formSubmitted));
  }

  getError(field: string): string {
    return this.formErrors[field] || '';
  }

  onSubmit() {
    if (!this.validateForm()) {
      return;
    }

    this.loading = true;
    this.error = null;

    this.authService.login(this.email, this.password).subscribe({
      next: (response) => {
        if (response.success) {
          this.router.navigate([this.returnUrl]);
        } else {
          this.error = response.message || 'Error al iniciar sesión';
          this.loading = false;
        }
      },
      error: (err) => {
        this.error = err.error?.message || 'Credenciales inválidas. Por favor verifica tu email y contraseña.';
        this.loading = false;
      }
    });
  }
}
