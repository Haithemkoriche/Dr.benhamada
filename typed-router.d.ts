/* eslint-disable */
/* prettier-ignore */
// @ts-nocheck
// Generated by unplugin-vue-router. ‼️ DO NOT MODIFY THIS FILE ‼️
// It's recommended to commit this file.
// Make sure to add this file to your tsconfig.json file as an "includes" or "files" entry.

declare module 'vue-router/auto-routes' {
  import type {
    RouteRecordInfo,
    ParamValue,
    ParamValueOneOrMore,
    ParamValueZeroOrMore,
    ParamValueZeroOrOne,
  } from 'unplugin-vue-router/types'

  /**
   * Route name map generated by unplugin-vue-router
   */
  export interface RouteNamedMap {
    'root': RouteRecordInfo<'root', '/', Record<never, never>, Record<never, never>>,
    '$error': RouteRecordInfo<'$error', '/:error(.*)', { error: ParamValue<true> }, { error: ParamValue<false> }>,
    'consultations-add': RouteRecordInfo<'consultations-add', '/consultations/add', Record<never, never>, Record<never, never>>,
    'consultations-list': RouteRecordInfo<'consultations-list', '/consultations/list', Record<never, never>, Record<never, never>>,
    'login': RouteRecordInfo<'login', '/login', Record<never, never>, Record<never, never>>,
    'patients': RouteRecordInfo<'patients', '/patients', Record<never, never>, Record<never, never>>,
    'patients-liste': RouteRecordInfo<'patients-liste', '/patients/liste', Record<never, never>, Record<never, never>>,
    'payments': RouteRecordInfo<'payments', '/payments', Record<never, never>, Record<never, never>>,
    'rdvs-rdvs': RouteRecordInfo<'rdvs-rdvs', '/rdvs/rdvs', Record<never, never>, Record<never, never>>,
    'salles-attente': RouteRecordInfo<'salles-attente', '/salles/attente', Record<never, never>, Record<never, never>>,
    'salles-historique': RouteRecordInfo<'salles-historique', '/salles/historique', Record<never, never>, Record<never, never>>,
    'second-page': RouteRecordInfo<'second-page', '/second-page', Record<never, never>, Record<never, never>>,
  }
}
