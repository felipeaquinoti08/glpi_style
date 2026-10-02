# GLPI Style

**Identidade visual completa para o GLPI 11: tela de login premium, logos, favicon, cores e um visual interno moderno. Tudo editável pelo navegador, com prévia ao vivo e sem alterar nenhum arquivo do GLPI.**

![GLPI](https://img.shields.io/badge/GLPI-11.0.x-2f6fed)
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4)
![Licença](https://img.shields.io/badge/licen%C3%A7a-GPL--3.0--or--later-green)

<!--
Capturas de tela: adicione as imagens em docs/img/ e descomente.
![Tela de login](docs/img/login.png)
![Editor com prévia ao vivo](docs/img/editor.png)
-->

---

## Sumário

- [Por que usar](#por-que-usar)
- [Recursos](#recursos)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Primeiros passos](#primeiros-passos)
- [Receitas de tela de login](#receitas-de-tela-de-login)
- [Atualização](#atualização)
- [Desativar ou desinstalar](#desativar-ou-desinstalar)
- [Onde ficam os dados](#onde-ficam-os-dados)
- [Compatibilidade](#compatibilidade)
- [Segurança e privacidade](#segurança-e-privacidade)
- [Solução de problemas](#solução-de-problemas)
- [Como funciona por dentro](#como-funciona-por-dentro)
- [Contribuindo](#contribuindo)
- [Licença](#licença)

---

## Por que usar

- **Nada de editar arquivos do GLPI.** O plugin não sobrescreve templates nem CSS do core. Atualize o GLPI sem medo de perder a personalização.
- **Tudo pela interface.** Uma página de configuração com seções organizadas, ajuda em cada campo e **prévia ao vivo** antes de salvar.
- **Sem configuração, nada muda.** Cada parte (login, cores, melhorias do visual interno) só age quando você liga. O que ficar em "Usar padrão do tema" continua exatamente como o GLPI desenha.
- **Respeita o tema de cada usuário.** As cores e melhorias são aplicadas por cima da paleta que cada pessoa escolheu no GLPI, inclusive paletas escuras.

---

## Recursos

### Tela de login

| Grupo | O que dá para personalizar |
|---|---|
| **Posição** | Caixa de login em qualquer ponto de uma grade 3×3 sobre o fundo, ou painel lateral de altura total (esquerdo ou direito). Largura pequena, média ou grande. |
| **Caixa** | Cor de fundo, opacidade, efeito de vidro fosco, cores do título e dos textos, tema claro ou escuro, fonte (Inter, Plus Jakarta Sans, Manrope, Poppins, Montserrat ou fonte do sistema), arredondamento e alinhamento. |
| **Logo** | Imagem própria com largura e altura, dentro da caixa ou acima dela, direto sobre o fundo. |
| **Fundo** | Imagem com ajuste (cobrir a tela, mostrar inteira, tamanho original ou mosaico) e ponto de foco. Gradiente de 3 cores com ângulo ajustável, no lugar da imagem ou por cima dela, com intensidade regulável. |
| **Efeitos** | Textura de pontos ou grade, formas de luz desfocadas e animação suave, desligada automaticamente para quem prefere menos movimento (`prefers-reduced-motion`). |
| **Painel de destaque** | Selo, título, subtítulo e lista de destaques sobre o fundo, posicionados numa grade 3×3 como a caixa de login. Largura estreita, média ou larga, alinhamento automático ou fixo, e fundo de vidro opcional para dar leitura sobre fotos. Aparece nos layouts de painel lateral e, se você quiser, também com a caixa flutuante. |
| **Textos** | Título, mensagem, texto do botão, separador antes de outros logins (SSO) e linha abaixo do título. |
| **Botão** | Cor própria com texto em contraste automático, estilo gradiente com brilho ou cor sólida. |
| **Campos** | Ícones nos campos e botão de mostrar/ocultar senha. |
| **Rodapé** | Copyright do GLPI, texto próprio, os dois ou nenhum. |

### Página interna

- **Logo do cabeçalho** (com largura e altura) e **logo do menu recolhido**.
- **Favicon** da aba do navegador.
- **Cores**: primária, secundária, links, menu (fundo e texto) e cabeçalho (fundo e texto), cada uma com "Usar padrão do tema".
- **Presets de cores** prontos: Oceano, Grafite, Esmeralda, Violeta, Laranja e Vermelho.
- Com um menu claro e sem logo próprio, o logo do GLPI troca sozinho para a versão escura, para continuar visível.

### Melhorias do visual interno

Quatro melhorias independentes. Cada uma tem a sua chave "Ativar esta melhoria" e começa desligada.

| Melhoria | O que faz |
|---|---|
| **Menu moderno** | Item ativo em pílula suave, pílula sólida, indicador lateral ou minimalista, na cor primária ou numa cor própria. Itens arredondados, hover suave, submenus mais limpos e espaçamento ajustável. Para o menu horizontal, a barra do topo pode ter cor sólida, gradiente ou vidro translúcido, com sombra e submenus em cartão. |
| **Cards e formulários** | Arredondamento e sombra dos cards. Títulos de seção simples, com barra de destaque ou com fundo colorido. Campos compactos, normais ou grandes, com foco na cor primária. Arredondamento dos botões, abas no padrão, com aba ativa colorida ou em pílulas, e barra de Salvar fixa no rodapé em formulários longos. |
| **Listas e tabelas** | Densidade das linhas (automática como o GLPI, compacta, normal ou confortável), linha destacada sob o mouse, zebrado opcional, cabeçalho em maiúsculas discretas ou colorido, links neutros, lista com cantos arredondados e paginação em pílulas. |
| **Fonte e densidade** | Fonte do GLPI inteiro, tamanho base de 12 a 17 px, espaçamento compacto, normal ou confortável, e títulos mais fortes. |

### E-mails

O GLPI coloca no fim de **todo** e-mail a assinatura ("-- Assinatura") e a linha "Automaticamente gerado por GLPI", que o próprio GLPI não deixa desligar. O GLPI Style tira as duas de todos os e-mails: chamados, testes de notificação e plugins. As duas opções já vêm ligadas e podem ser desligadas na seção **E-mails** da configuração.

### Editor

- Página **Identidade visual**, organizada em seções recolhíveis, com ajuda (**?**) em cada campo.
- **Prévia sob demanda** em uma gaveta lateral, fechada até você clicar em **Prévia**. Ela mostra a **tela de login** e páginas internas reais (**página inicial, lista de chamados, formulário de chamado e lista de computadores**) com as alterações ainda não salvas, nos formatos computador, tablet e celular. Nas páginas internas dá para passar o mouse e ver os efeitos; links e envios de formulário ficam desativados.
- Aviso ao sair da página com alterações não salvas, e botão **Restaurar padrão** (as imagens enviadas são mantidas).

---

## Requisitos

- **GLPI 11.0.x**
- **PHP 8.2 ou superior**, com a extensão `fileinfo` (vem habilitada por padrão)
- Permissão de **Configuração > Atualizar** para acessar o editor

---

## Instalação

1. Baixe o plugin para a pasta `plugins` do GLPI. **A pasta precisa se chamar `glpistyle`**, sem hífen nem sublinhado:

   ```bash
   cd /var/www/glpi/plugins
   git clone https://github.com/felipeaquinoti08/glpi_style.git glpistyle
   ```

   Se preferir, baixe o ZIP em **Code > Download ZIP** no GitHub, extraia e renomeie a pasta para `glpistyle`.

2. Ajuste o dono dos arquivos para o usuário do servidor web (em geral `www-data`):

   ```bash
   chown -R www-data:www-data glpistyle
   ```

3. No GLPI, vá em **Configurar > Plugins**, clique em **Instalar** e depois em **Ativar** no **GLPI Style**.

   Pela linha de comando, a partir da pasta do GLPI:

   ```bash
   sudo -u www-data php bin/console plugin:install --username=glpi glpistyle
   sudo -u www-data php bin/console plugin:activate glpistyle
   ```

   Troque `glpi` pelo login de um administrador da sua instalação.

4. Clique no ícone de engrenagem do plugin para abrir o editor.

### Instalação com Docker

A pasta `plugins` do GLPI costuma estar num volume. Clone dentro dele e rode os comandos do console dentro do contêiner:

```bash
docker exec -u www-data <contêiner-do-glpi> php bin/console plugin:install --username=glpi glpistyle
docker exec -u www-data <contêiner-do-glpi> php bin/console plugin:activate glpistyle
```

---

## Primeiros passos

1. Abra **Configurar > Plugins > GLPI Style** (engrenagem).
2. Em **Tela de login**, envie o logo e a imagem de fundo e escolha a posição da caixa. Clique em **Prévia** para acompanhar.
3. Em **Página interna**, envie o logo do cabeçalho e o favicon.
4. Em **Cores**, escolha um preset ou ajuste as cores uma a uma.
5. Ligue as **melhorias do visual interno** que quiser. Na **Prévia**, escolha uma página interna (lista de chamados, formulário...) para ver o efeito antes de salvar.
6. Clique em **Salvar**.

> **Dica:** imagens novas só aparecem na prévia depois de salvar. Cores, textos e posições aparecem na hora.

---

## Receitas de tela de login

| Visual | Como montar |
|---|---|
| **Imagem com painel lateral** | Imagem de fundo + posição **Painel lateral direito** (ou esquerdo). Deixe os textos do painel de destaque em branco para mostrar só a imagem, e ligue **Linha abaixo do título**. |
| **Cartão central sobre a imagem** | Imagem de fundo + posição **Centro da tela** + logo **Acima da caixa, sobre o fundo**. |
| **Cartão de vidro** | Imagem de fundo + posição **Meio, à esquerda** + opacidade do fundo da caixa em torno de 60% + vidro fosco em 10 px. |
| **Visual escuro** | Tema do formulário **Escuro** + **Painel lateral direito** + fundo da caixa em preto. |
| **Sem imagem** | Não envie imagem de fundo: o gradiente de 3 cores vira o fundo, com textura, formas de luz e animação. |

---

## Atualização

```bash
cd /var/www/glpi/plugins/glpistyle
git pull
chown -R www-data:www-data .
```

Depois recarregue as páginas com **Ctrl+F5**. As configurações e as imagens enviadas são mantidas, e configurações de versões anteriores são convertidas automaticamente.

Se a atualização trouxer uma nova versão do plugin, o GLPI passa a indicar em **Configurar > Plugins** que ele precisa ser atualizado. Clique em **Atualizar** e ative de novo.

---

## Desativar ou desinstalar

- **Desativar** volta o GLPI ao visual padrão na hora e **mantém** as configurações e as imagens. Ative de novo quando quiser.
- **Desinstalar** **apaga** as configurações e as imagens enviadas. Faça backup antes, se quiser reaproveitá-las.

---

## Onde ficam os dados

| O quê | Onde |
|---|---|
| Configurações | Banco de dados do GLPI, tabela `glpi_configs`, contexto `plugin:glpistyle` |
| Imagens enviadas | `files/_plugins/glpistyle/` na pasta de dados do GLPI (`GLPI_VAR_DIR`) |

Nada disso fica na pasta do plugin nem vai para o repositório. Um backup normal do banco e da pasta de dados do GLPI já cobre a personalização.

---

## Compatibilidade

- **Plugins de login (SSO)**: botões que outros plugins adicionam à tela de login pelo hook `display_login` continuam funcionando. Eles aparecem abaixo do formulário, depois do separador configurável. O [Entrasso](https://github.com/felipeaquinoti08/entrasso) (SSO com Microsoft Entra ID) já se integra a esse layout.
- **Texto de login do próprio GLPI** (definido em *Configurar > Geral*): continua sendo exibido dentro da caixa.
- **Menu vertical e horizontal**: ambos são suportados. Com o menu horizontal, a barra do topo segue as cores do **Menu**. As cores de **Cabeçalho** valem para a barra de busca e usuário, que só existe com o menu vertical.
- **Paletas do GLPI**, inclusive escuras: tudo que fica em "Usar padrão do tema" segue a paleta de cada usuário.
- **Servidores web**: o CSS e o JavaScript do plugin passam por um script PHP próprio. Por isso funcionam mesmo em Nginx configurado para servir `.css`/`.js` apenas a partir de `glpi/public`, o que costuma quebrar os arquivos estáticos de plugins.

---

## Segurança e privacidade

- Todos os valores que entram no CSS gerado são validados no servidor: cor hexadecimal, número dentro de limites ou opção de uma lista fechada. Textos são escapados antes de ir para a página.
- **Uploads**: a extensão e o conteúdo real do arquivo são conferidos. SVGs com scripts, eventos ou referências externas são recusados. O limite é de 8 MB por imagem.
- As imagens são servidas com `X-Content-Type-Options: nosniff` e uma CSP restritiva.
- **Endpoints públicos**: `front/asset.php`, `front/resource.php` e `front/style.css.php` precisam ser públicos, porque a tela de login os usa antes da autenticação. Eles só entregam os arquivos do próprio plugin, a partir de uma lista fixa. O editor, a prévia e a prévia do visual interno exigem o direito **Configuração > Atualizar**.
- **Google Fonts**: se você escolher uma fonte diferente de "Fonte do sistema" (login) ou "Padrão do GLPI" (visual interno), o navegador de cada usuário baixa a fonte de `fonts.googleapis.com`. Em ambientes sem acesso à internet, ou com política de privacidade restritiva, mantenha essas opções padrão.

---

## Solução de problemas

<details>
<summary><strong>Mudei algo e não aparece</strong></summary>

Recarregue com **Ctrl+F5**. As URLs dos arquivos mudam a cada salvamento, mas o navegador pode ter guardado a página em cache. Confirme também que você clicou em **Salvar**: a prévia mostra alterações ainda não salvas.
</details>

<details>
<summary><strong>A tela de login voltou ao padrão do GLPI</strong></summary>

Verifique:

- se a opção **Ativar a nova tela de login** está ligada;
- se o plugin está ativo em **Configurar > Plugins**.

Depois de atualizar o plugin, o GLPI pode indicar que ele precisa ser atualizado: clique em **Atualizar** e ative de novo.
</details>

<details>
<summary><strong>A página de configuração aparece desalinhada, sem estilo</strong></summary>

Abra a ferramenta de desenvolvedor do navegador (F12) e veja, na aba **Rede**, se `front/resource.php?f=config.css` responde 200. Um erro 404 ou 403 indica uma regra do servidor web ou do proxy bloqueando scripts PHP dentro de `plugins/`.
</details>

<details>
<summary><strong>O envio da imagem falha</strong></summary>

O plugin aceita até 8 MB. Confira também os limites do PHP (`upload_max_filesize` e `post_max_size`) e do servidor web (`client_max_body_size` no Nginx). A mensagem de erro no topo da página indica o motivo: formato, tamanho ou conteúdo inválido.
</details>

<details>
<summary><strong>O logo do GLPI sumiu depois de mudar as cores</strong></summary>

O logo padrão do GLPI é branco. O plugin troca para a versão escura quando o **Menu - fundo** é claro. Se estiver usando um logo próprio, envie uma versão que contraste com a cor do menu.
</details>

<details>
<summary><strong>Alguma tela ficou estranha com uma melhoria ligada</strong></summary>

Desligue a melhoria correspondente no editor: aquela parte volta exatamente ao padrão do GLPI. Depois abra uma [issue](https://github.com/felipeaquinoti08/glpi_style/issues) com um print da tela, a página em que aconteceu e a versão do GLPI.
</details>

---

## Como funciona por dentro

- **Tela de login**: o plugin usa o hook `display_login` do GLPI. Um script pequeno reorganiza a página enquanto ela carrega, antes do primeiro desenho da tela. O template do core não é tocado.
- **Visual interno**: o CSS é carregado pelos hooks `add_css`. As regras estáticas só consomem variáveis CSS, e os valores configurados são gerados por `front/style.css.php`. Cards, botões e tabelas são ajustados pelas próprias variáveis do Tabler (framework visual do GLPI 11), evitando brigar com as regras do core.
- **Prévias**: `front/preview.php` renderiza o template real de login do GLPI com os valores ainda não salvos. Para as páginas internas, a prévia carrega a página real do GLPI e substitui nela o CSS do plugin pelo que `front/live.css.php` calcula a partir desses mesmos valores.

### Estrutura

```
glpistyle/
├── setup.php                 # registro do plugin e dos hooks
├── install/                  # instalação e desinstalação
├── front/
│   ├── config.php            # editor "Identidade visual"
│   ├── preview.php           # prévia da tela de login
│   ├── live.css.php          # CSS não salvo para a prévia das páginas internas
│   ├── style.css.php         # CSS gerado (logos, favicon, cores, melhorias)
│   ├── asset.php             # entrega das imagens enviadas
│   └── resource.php          # entrega do CSS/JS do plugin
├── src/
│   ├── Config.php            # campos, validação e armazenamento
│   ├── Style.php             # geração de CSS
│   ├── Login.php             # conteúdo da tela de login
│   └── Ui/                   # melhorias do visual interno
│       ├── Feature.php       # classe base
│       ├── Registry.php      # descobre as melhorias
│       └── *Feature.php      # uma por melhoria
└── public/
    ├── css/                  # login.css, config.css, ui/*.css
    └── js/                   # login.js, config.js, favicon.js
```

---

## Contribuindo

Sugestões, relatos de problemas e pull requests são bem-vindos.

- **Problemas**: abra uma [issue](https://github.com/felipeaquinoti08/glpi_style/issues) com a versão do GLPI, o navegador, os passos para reproduzir e um print.
- **Pull requests**: uma branch por assunto, a partir da `main`. Mantenha o estilo do código ao redor.

### Criando uma nova melhoria do visual interno

As melhorias são descobertas automaticamente, então uma nova não mexe nos arquivos compartilhados:

1. Crie `src/Ui/MinhaFeature.php`, estendendo `GlpiPlugin\Glpistyle\Ui\Feature`, e implemente:
   - `key()` (ex.: `minha`), `order()`, `title()`, `subtitle()` e `icon()`;
   - `fields()`, com os campos no formato `nome => [tipo, padrão, extra]`. Os tipos são `bool`, `color`, `color_opt`, `int`, `enum`, `text` e `lines`. Use o prefixo `ui_minha_`. A chave `ui_minha_enabled` é criada sozinha e começa desligada.
   - `section($ui, $config)`, que monta a seção do editor com os renderizadores recebidos em `$ui` (`card`, `switch`, `select`, `range`, `color`, `color_opt`...);
   - `css($config)`, que gera o CSS a partir de valores já validados.
2. Se precisar de regras fixas, crie `public/css/ui/minha.css`. Ele é carregado apenas enquanto a melhoria estiver ligada.
3. Nunca coloque no CSS um valor que não tenha passado pela validação dos campos.

### Outros plugins gratuitos do mesmo autor

- [Entrasso](https://github.com/felipeaquinoti08/entrasso): login único (SSO) com Microsoft Entra ID, com criação e sincronização automática de usuários.
- [Termodocs](https://github.com/felipeaquinoti08/termodoc): termos de entrega e devolução de equipamentos com assinatura eletrônica, lembretes por e-mail e link seguro para quem não entra no GLPI.
- [Sentinela](https://github.com/felipeaquinoti08/sentinela): auditoria de softwares instalados, com políticas de lista branca e lista negra e alertas por e-mail.

Todos são gratuitos e distribuídos sob a mesma licença (GPL-3.0-or-later).

---

## Licença

Distribuído sob a **GNU General Public License v3.0 ou posterior**. Veja o arquivo [LICENSE](LICENSE).

Desenvolvido por **Felipe Aquino**.
