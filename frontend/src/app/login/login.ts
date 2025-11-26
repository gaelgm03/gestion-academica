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

  onSubmit() {
    if (!this.email || !this.password) {
      this.error = 'Por favor ingresa tu email y contraseña';
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
