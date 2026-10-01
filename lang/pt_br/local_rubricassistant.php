<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * local_rubricassistant.php
 *
 * @package   local_rubricassistant
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['accept'] = 'Aceitar';
$string['acceptchange'] = 'Aceitar alteração proposta';
$string['aierror'] = 'A solicitação de IA não pôde ser concluída: {$a}';
$string['alignment'] = 'Alinhamento';
$string['applied'] = 'As sugestões selecionadas foram aplicadas usando a API oficial de avaliação avançada do Moodle.';
$string['applyselected'] = 'Aplicar sugestões selecionadas';
$string['assignment'] = 'Atividade';
$string['back'] = 'Voltar ao assistente';
$string['basischanged'] = 'A atividade ou o formulário de avaliação mudou depois que este rascunho foi gerado. Gere uma nova análise antes de aplicar sugestões.';
$string['bridgeunavailable'] = 'A API obrigatória local_ai_bridge não está disponível.';
$string['cannotapply'] = 'Este rascunho pode ser revisado, mas não pode ser aplicado porque o método de avaliação avançada atual é incompatível.';
$string['competencies'] = 'Competências';
$string['criterion'] = 'Critério';
$string['criteriondescription'] = 'Descrição do critério';
$string['criterionname'] = 'Nome do critério';
$string['current'] = 'Critério atual';
$string['currentmethod'] = 'Método de avaliação avançada atual';
$string['draftmissing'] = 'Este rascunho não existe mais, expirou ou pertence a outra sessão.';
$string['duplicatecriterion'] = 'A IA retornou critérios duplicados.';
$string['expires'] = 'O rascunho expira em uma hora ou quando a sessão terminar.';
$string['findings'] = 'Achados';
$string['generate'] = 'Gerar análise';
$string['heading'] = 'Assistente de rubricas';
$string['intro'] = 'Crie ou revise um método de avaliação avançada com sugestões de IA, mantendo todas as decisões finais com o professor.';
$string['invalidairesponse'] = 'A resposta da IA não é válida para o Assistente de rubricas: {$a}';
$string['invalidcriterion'] = 'Um critério retornado pela IA é inválido.';
$string['invalidguide'] = 'Um critério de guia de avaliação precisa de nome, descrição e pontuação máxima positiva.';
$string['invalidjson'] = 'A resposta precisa ser um objeto JSON contendo criteria, findings e alignment.';
$string['invalidlevels'] = 'Um critério de rubrica precisa ter pelo menos dois níveis válidos, sem pontuações duplicadas.';
$string['leveldefinition'] = 'Descrição do nível';
$string['markers'] = 'Orientação para avaliadores';
$string['maxscore'] = 'Pontuação máxima';
$string['message'] = 'Mensagem';
$string['method:guide'] = 'Guia de avaliação';
$string['method:rubric'] = 'Rubrica';
$string['methodchanged'] = 'O método de avaliação avançada mudou depois que este rascunho foi criado. Reabra o assistente antes de aplicar sugestões.';
$string['newcriterion'] = 'Novo critério';
$string['nochangesproposed'] = 'A análise não retornou alterações de critérios para aplicar. Os achados e o alinhamento ainda podem ser revisados acima.';
$string['nocompetencies'] = 'Nenhuma competência vinculada foi encontrada ou está visível para este usuário.';
$string['nocriteria'] = 'Nenhum critério está definido atualmente.';
$string['none'] = 'Nenhum';
$string['nosubmissions'] = 'Submissões de alunos não são lidas nem enviadas à IA nesta versão.';
$string['nothingselected'] = 'Nenhum critério foi selecionado.';
$string['objectives'] = 'Objetivos de aprendizagem adicionais';
$string['objectives_help'] = 'Objetivos opcionais em texto simples que ainda não estejam representados pelas competências do Moodle.';
$string['operation'] = 'Operação';
$string['operation:compare'] = 'Comparar método de avaliação com a atividade';
$string['operation:create'] = 'Criar um rascunho';
$string['operation:review'] = 'Revisar o método de avaliação atual';
$string['pluginname'] = 'Assistente de rubricas';
$string['privacy:metadata'] = 'O Assistente de rubricas não armazena dados pessoais em tabelas próprias persistentes. Rascunhos ficam somente na sessão atual.';
$string['proposal'] = 'Critério proposto';
$string['rationale'] = 'Motivo da sugestão';
$string['regradeconfirm'] = 'Entendo que mudanças estruturais podem exigir revisão ou reavaliação de avaliações já realizadas.';
$string['regradeconfirmationrequired'] = 'Estas mudanças afetam a estrutura de avaliação e já existem avaliações. Confirme o aviso de reavaliação antes de aplicá-las.';
$string['reject'] = 'Rejeitar';
$string['reviewnotice'] = 'Nada é gravado no Moodle até que você selecione explicitamente sugestões e mande aplicá-las.';
$string['reviewtitle'] = 'Revisar sugestões da IA';
$string['rubricassistant:use'] = 'Usar o assistente de rubricas';
$string['score'] = 'Pontuação';
$string['severity'] = 'Severidade';
$string['sourcecriterionmissing'] = 'A IA referenciou um critério que não existe no método de avaliação atual.';
$string['statement'] = 'Enunciado da atividade';
$string['summary'] = 'Resumo do método de avaliação atual';
$string['targetmethod'] = 'Método de avaliação';
$string['teachercriteria'] = 'Critérios informados pelo professor';
$string['teachercriteria_help'] = 'Notas opcionais em texto simples descrevendo critérios, evidências esperadas, prioridades ou restrições.';
$string['type'] = 'Tipo';
$string['unsupportedmethod'] = 'O método de avaliação avançada ativo não é suportado por esta versão do Assistente de rubricas.';
$string['weight'] = 'Proporção efetiva';
