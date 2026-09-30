# GLPI Style

Plugin para GLPI 11 que deixa a **tela de login** com visual premium e totalmente editável, e troca **logos e favicon** do GLPI inteiro.

## Recursos

**Tela de login**
- 3 layouts: dividido com painel de destaque à esquerda, à direita, ou centralizado (cartão de vidro sobre o fundo). Em telas pequenas, todos viram o cartão de vidro.
- Fundo em gradiente de 3 cores com ângulo ajustável, foto opcional com intensidade do gradiente por cima, textura (pontos ou grade), formas de luz desfocadas e animação suave (respeita `prefers-reduced-motion`).
- Painel de destaque com selo, título, subtítulo e lista de destaques.
- Formulário com tema claro ou escuro, cor de destaque, arredondamento, fonte (Inter, Plus Jakarta Sans, Manrope, Poppins, Montserrat ou fonte do sistema), ícones nos campos, botão de mostrar senha, título, subtítulo e texto do botão personalizáveis.
- Botões de outros plugins de login (ex.: SSO do Entrasso) aparecem abaixo do formulário, com um separador configurável.
- Texto de rodapé próprio e opção de ocultar o copyright do GLPI.

**GLPI inteiro**
- Logo do menu lateral (expandido e recolhido).
- Favicon.

**Editor**
- Página de configuração com **prévia ao vivo** (computador, tablet e celular) que atualiza enquanto você edita, antes de salvar.

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
- `front/asset.php` e `front/style.css.php` são públicos (a tela de login precisa deles antes da autenticação) e só servem os arquivos do próprio plugin.
- Todo valor que entra no CSS gerado é validado (cor hexadecimal, número com limite ou opção de uma lista). SVGs com scripts ou referências externas são recusados no envio.

## Desinstalação

Desinstalar o plugin apaga as configurações e as imagens enviadas.

## Licença

GPL-3.0-or-later
