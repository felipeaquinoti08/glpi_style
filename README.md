# GLPI Style

Plugin para GLPI 11 que deixa a **tela de login** com visual premium e totalmente editável, e troca **logos e favicon** do GLPI inteiro.

## Recursos

**Tela de login**
- Posição da caixa em uma grade 3×3 sobre o fundo, ou painel lateral de altura total (esquerdo ou direito), com largura pequena, média ou grande.
- Fundo da caixa com cor, opacidade e vidro fosco. Cores de título e textos, com opção de "usar padrão do tema".
- Logo com largura/altura, dentro da caixa ou acima dela.
- Imagem de fundo com ajuste (cobrir, mostrar inteira, tamanho original, mosaico) e ponto de foco. Gradiente de 3 cores por cima (ou no lugar da imagem), textura, formas de luz e animação suave (respeita `prefers-reduced-motion`).
- Painel de destaque com selo, título, subtítulo e lista de destaques nos layouts de painel lateral.
- Título, mensagem, texto do botão, estilo do botão (gradiente ou sólido), tema claro/escuro, fonte, alinhamento, arredondamento, linha abaixo do título e botão de mostrar senha.
- Rodapé: copyright do GLPI, texto próprio, os dois, ou oculto.
- Botões de outros plugins de login (ex.: SSO do Entrasso) aparecem abaixo do formulário, com separador configurável.

**Página interna**
- Logo do cabeçalho (com largura/altura), logo do menu recolhido e favicon.
- Cores primária, secundária, links, menu lateral (fundo/texto) e cabeçalho (fundo/texto), com presets, aplicadas por cima do tema de cada usuário.

**Editor**
- Página "Identidade visual" em seções recolhíveis, com prévia ao vivo da tela de login (computador, tablet e celular) antes de salvar.

## Instalação

```bash
cd /caminho/do/glpi/plugins
git clone https://github.com/felipeaquinoti08/glpi_style.git glpistyle
chown -R www-data:www-data glpistyle
```

A pasta **precisa** se chamar `glpistyle`. Depois, em **Configurar > Plugins**, instale e ative o **GLPI Style** e clique na engrenagem para configurar.

Pela linha de comando:

```bash
php bin/console plugin:install --username=glpi glpistyle
php bin/console plugin:activate glpistyle
```

## Requisitos

- GLPI 11.0.x
- PHP com a extensão `fileinfo` (padrão)

## Como funciona

- As configurações ficam em `glpi_configs` (contexto `plugin:glpistyle`). As imagens enviadas ficam em `files/_plugins/glpistyle/`.
- A tela de login não sobrescreve o template do core: o plugin usa o hook `display_login` e reorganiza a página com CSS e um pequeno script. Isso mantém a compatibilidade com atualizações do GLPI e com outros plugins de login.
- CSS e JS do plugin são servidos por `front/resource.php`, então funcionam mesmo com servidores configurados para responder `.css`/`.js` só a partir de `glpi/public`.
- `front/asset.php`, `front/resource.php` e `front/style.css.php` são públicos (a tela de login precisa deles antes da autenticação) e só servem os arquivos do próprio plugin.
- Todo valor que entra no CSS gerado é validado (cor hexadecimal, número com limite ou opção de uma lista). SVGs com scripts ou referências externas são recusados no envio.

## Desinstalação

Desinstalar o plugin apaga as configurações e as imagens enviadas.

## Licença

GPL-3.0-or-later
