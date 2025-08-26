</main>

    <footer>
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-5 col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Sistema de gerenciamento desenvolvido para controle de produtos, vendas e clientes.</p>
                    <div class="social-icons d-flex mt-4">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Links Rápidos</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/index.php">Início</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/produtos.php">Produtos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/pedidos.php">Pedidos</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/relatorios.php">Relatórios</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Cadastros</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/pages/clientes.php">Clientes</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/categorias.php">Categorias</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/marcas.php">Marcas</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/pages/fornecedores.php">Fornecedores</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contato</h5>
                    <ul>
                        <li><i class="fas fa-envelope me-2"></i> contato@faculdade.com</li>
                        <li><i class="fas fa-phone me-2"></i> (11) 1234-5678</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> São Paulo, SP</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    <!-- Effects JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/effects.js"></script>
</body>
</html>