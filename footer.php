<?php
// footer.php - Footer padrão sem JavaScript
$anoAtual = date('Y');
?>

<footer class="footer">
    <div class="footer-container">
        <!-- Seção principal com 2 colunas -->
        <div class="footer-main">
            <!-- Coluna da esquerda: Logo, descrição e redes sociais -->
            <div class="footer-left">
                <div class="footer-logo-section">
                    <div class="logo-footer">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Agenda Cultural</span>
                    </div>
                    
                    <p class="footer-description">
                        Plataforma para descobrir e gerenciar eventos culturais. 
                        Conecte-se com a cultura da sua cidade.
                    </p>
                </div>
                
                <!-- Redes sociais -->
                <div class="footer-social">
                    <a href="https://www.facebook.com/ulbrasaolucas" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/ulbrasaolucas/" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/c/Col%C3%A9gioULBRAS%C3%A3oLucas" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Coluna da direita: Contato -->
            <div class="footer-right">
                <div class="footer-contact-section">
                    <h3 class="contact-title">Contato</h3>
                    <ul class="contact-list">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:ulbrasaolucas@ulbra.br">ulbrasaolucas@ulbra.br</a>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <!-- https://wa.me/<+555134517557> -->
                            <a href="https://wa.me/+555134517557">(51) 3451-7557</a>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Sapucaia do Sul - RS</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span>Segunda a Sexta: 7h às 22h</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Linha divisória -->
        <div class="footer-divider"></div>
        
        <!-- Seção inferior: Copyright -->
        <div class="footer-bottom">
            <div class="copyright">
                <p>
                    <i class="far fa-copyright"></i> 
                    <span class="copyright-year"><?php echo $anoAtual; ?></span> Agenda Cultural
                </p>
                <p>Todos os direitos reservados.</p>
            </div>
        </div>
    </div>
</footer>