import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterOutlet, Router } from '@angular/router';
import { Navbar } from './navbar/navbar';
import { AuthService } from './services/auth.service';

@Component({
  selector: 'app-root',
  imports: [CommonModule, RouterOutlet, Navbar],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements OnInit {
  isAuthenticated = false;
  showNavbar = true;

  constructor(
    private authService: AuthService,
    private router: Router
  ) {
    // Ocultar navbar en la página de login
    this.router.events.subscribe(() => {
      this.showNavbar = !this.router.url.includes('/login');
    });
  }

  ngOnInit() {
    this.authService.currentUser.subscribe(user => {
      this.isAuthenticated = !!user;
    });
  }
}
